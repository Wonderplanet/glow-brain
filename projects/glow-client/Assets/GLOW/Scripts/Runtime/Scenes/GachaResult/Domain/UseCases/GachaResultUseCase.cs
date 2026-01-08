using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Extensions;
using GLOW.Modules.InAppReview.Domain.ValueObject;
using GLOW.Scenes.GachaContent.Domain.Calculator;
using GLOW.Scenes.GachaList.Domain.Evaluator;
using GLOW.Scenes.GachaResult.Domain.Model;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.GachaResult.Domain.UseCases
{
    public class GachaResultUseCase
    {
        [Inject] IGachaCacheRepository GachaCacheRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] IOprGachaUseResourceRepository OprGachaUseResourceRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IInAppReviewPreferenceRepository InAppReviewPreferenceRepository { get; }
        [Inject] IGachaEvaluator GachaEvaluator { get; }

        public GachaResultUseCaseModel GetGachaResultUseCase()
        {
            // キャッシュからガチャ結果を取得
            var resultCache = GachaCacheRepository.GetGachaResultModels();
            // ガシャ演出・結果表示用モデルのキャッシュの消去
            GachaCacheRepository.ClearGachaResultModels();

            var resultModels = new List<GachaResultResourceModel>();
            var convertedModels = new List<GachaResultResourceModel>();
            var avatarModels = new List<GachaResultAvatarModel>();
            var existsPreConversionResource = PreConversionResourceExistenceFlag.False;
            
            foreach (var model in resultCache)
            {
                bool hasUnit = false;
                // ユニットの場合新規入手のため、アバター表示用モデルを追加
                if (model.ResourceType == ResourceType.Unit)
                {
                    var assetKey = MstCharacterDataRepository.GetCharacter(model.ResourceId).AssetKey;
                    var assetPath = CharacterIconAssetPath.FromAssetKey(assetKey);
                    var avatarModel = new GachaResultAvatarModel(assetPath);
                    avatarModels.Add(avatarModel);
                    hasUnit = true;
                }

                var resultModel = new GachaResultResourceModel(
                    PlayerResourceModelFactory.Create(
                        model.ResourceType,
                        model.ResourceId,
                        model.ResourceAmount.ToPlayerResourceAmount()
                    ),
                    new IsNewUnitBadge(hasUnit)
                );

                var preConversionResultModel = new GachaResultResourceModel(
                    PlayerResourceModelFactory.Create(
                        model.PreConversionResource.ResourceType,
                        model.PreConversionResource.ResourceId,
                        model.PreConversionResource.ResourceAmount.ToPlayerResourceAmount()
                    ),
                    new IsNewUnitBadge(hasUnit)
                );

                // かけら変換がある場合、変換前のリソースを表示
                if (!model.PreConversionResource.IsEmpty())
                {
                    // 変換前のユニットを入れる
                    resultModels.Add(preConversionResultModel);
                    // かけらを変換後に入れる
                    convertedModels.Add(resultModel);
                }
                else
                {
                    resultModels.Add(resultModel);
                    convertedModels.Add(GachaResultResourceModel.Empty);
                }

                if(!model.PreConversionResource.IsEmpty() && !existsPreConversionResource)
                {
                    existsPreConversionResource = PreConversionResourceExistenceFlag.True;
                }
            }

            // キャッシュから引いたガチャ情報取得
            var gachaDrawInfoModel = GachaCacheRepository.GetGachaDrawInfoModel();
            GachaCacheRepository.ClearGachaDrawType();

            var gachaDrawableFlag = CanDraw(gachaDrawInfoModel);

            var gachaDrawFromContentViewFlag = GachaCacheRepository.GetGachaDrawFromContentViewFlag();
            GachaCacheRepository.ClearGachaDrawFromContentViewFlag();


            var isInAppReviewDisplay = ShouldDisplayInAppReview(resultModels, gachaDrawInfoModel);
            if (isInAppReviewDisplay)
            {
                InAppReviewPreferenceRepository.SetIsAppReviewDisplayedAfterGachaUrDrawn(isInAppReviewDisplay);
            }

            var useCaseModel = new GachaResultUseCaseModel(
                    gachaDrawInfoModel,
                    gachaDrawableFlag,
                    gachaDrawFromContentViewFlag,
                    resultModels,
                    convertedModels,
                    avatarModels,
                    existsPreConversionResource,
                    isInAppReviewDisplay);
            return useCaseModel;
        }

        DrawableFlag CanDraw(GachaDrawInfoModel gachaDrawInfoModel)
        {
            if(gachaDrawInfoModel.IsEmpty()) return DrawableFlag.False;

            // チュートリアルガシャは必ず引き直し可能
            if (gachaDrawInfoModel.GachaType == GachaType.Tutorial) return DrawableFlag.True;

            var oprGachaModel = OprGachaRepository.GetOprGachaModelFirstOrDefaultById(gachaDrawInfoModel.GachaId);
            var gachaDrawableFlag = DrawableFlag.True;
            
            var userGachaModel = GameRepository.GetGameFetchOther().UserGachaModels.FirstOrDefault(
                model => model.OprGachaId == oprGachaModel.GachaId, 
                UserGachaModel.CreateById(oprGachaModel.GachaId));

            var useResourceModels = GetUseResourceModels(oprGachaModel.GachaId, gachaDrawInfoModel.GachaDrawType);

            var highestUseResourceModel = GachaContentCalculator.GetHighestPriorityUseResourceModel(
                useResourceModels, 
                GameRepository.GetGameFetch(),
                GameRepository.GetGameFetchOther(), 
                SystemInfoProvider.GetApplicationSystemInfo().PlatformId);
            
            // 広告ガシャの場合、引けるかどうかを判定してそのまま返す
            if (gachaDrawInfoModel.GachaDrawType == GachaDrawType.Ad)
            {
                var adGachaDrawableFlag = GachaEvaluator.IsAdGachaDrawable(oprGachaModel, userGachaModel);
                return adGachaDrawableFlag.ToDrawableFlag();
            }

            // アイテムでひく場合にアイテムが不足している場合
            if (highestUseResourceModel.CostType == CostType.Item)
            {
                gachaDrawableFlag = GachaEvaluator.IsGachaDrawable(
                    highestUseResourceModel,
                    GameRepository.GetGameFetch(),
                    GameRepository.GetGameFetchOther(),
                    SystemInfoProvider.GetApplicationSystemInfo().PlatformId,
                    oprGachaModel,
                    userGachaModel
                );
            }

            // プリズム以外で引いた場合、消費コストのタイプが前回と異なる場合はボタンを非表示にする
            if (gachaDrawInfoModel.CostType != CostType.Diamond &&
                highestUseResourceModel.CostType != gachaDrawInfoModel.CostType)
            {
                gachaDrawableFlag = DrawableFlag.False;
            }

            // 引ける回数の上限の場合
            if (GachaEvaluator.HasReachedDrawLimitedCount(oprGachaModel, userGachaModel))
            {
                gachaDrawableFlag = DrawableFlag.False;
            }

            return gachaDrawableFlag;
        }

        InAppReviewFlag ShouldDisplayInAppReview(
            IReadOnlyList<GachaResultResourceModel> resultModels,
            GachaDrawInfoModel gachaDrawInfoModel)
        {
            var isCheckInAppReviewDisplay = !InAppReviewPreferenceRepository.IsAppReviewDisplayedAfterGachaUrDrawn &&
                                            gachaDrawInfoModel.GachaType != GachaType.Tutorial;

            if (!isCheckInAppReviewDisplay) return InAppReviewFlag.False;
            
            var isUrDrawn = resultModels.Any(model => model.PlayerResourceModel.Rarity == Rarity.UR);
            return isUrDrawn ? InAppReviewFlag.True : InAppReviewFlag.False;

        }
        
        IReadOnlyList<OprGachaUseResourceModel> GetUseResourceModels(MasterDataId gachaId, GachaDrawType gachaDrawType)
        {
            if (gachaDrawType == GachaDrawType.Single)
            {
                return OprGachaUseResourceRepository.FindByGachaId(gachaId)
                    .Where(model => model.GachaDrawCount?.Value == 1)
                    .ToList();
            }
            else if (gachaDrawType == GachaDrawType.Multi)
            {
                return OprGachaUseResourceRepository.FindByGachaId(gachaId)
                    .Where(model => model.GachaDrawCount?.Value > 1)
                    .ToList();
            }
            
            return new List<OprGachaUseResourceModel>();
        }
    }
}
