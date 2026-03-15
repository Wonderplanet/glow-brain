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
using GLOW.Modules.CommonReceiveView.Domain.Model;
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
        [Inject] IOprStepUpGachaRepository OprStepUpGachaRepository { get; }
        [Inject] IOprStepUpGachaStepRepository OprStepUpGachaStepRepository { get; }
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
                if (model.RewardModel.ResourceType == ResourceType.Unit)
                {
                    var assetKey = MstCharacterDataRepository.GetCharacter(model.RewardModel.ResourceId).AssetKey;
                    var assetPath = CharacterIconAssetPath.FromAssetKey(assetKey);
                    var avatarModel = new GachaResultAvatarModel(assetPath);
                    avatarModels.Add(avatarModel);
                    hasUnit = true;
                }

                var resultModel = new GachaResultResourceModel(
                    PlayerResourceModelFactory.Create(
                        model.RewardModel.ResourceType,
                        model.RewardModel.ResourceId,
                        model.RewardModel.Amount
                    ),
                    new IsNewUnitBadge(hasUnit)
                );

                var preConversionResultModel = new GachaResultResourceModel(
                    PlayerResourceModelFactory.Create(
                        model.RewardModel.PreConversionResource.ResourceType,
                        model.RewardModel.PreConversionResource.ResourceId,
                        model.RewardModel.PreConversionResource.ResourceAmount.ToPlayerResourceAmount()
                    ),
                    new IsNewUnitBadge(hasUnit)
                );

                // かけら変換がある場合、変換前のリソースを表示
                if (!model.RewardModel.PreConversionResource.IsEmpty())
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

                if(!model.RewardModel.PreConversionResource.IsEmpty() && !existsPreConversionResource)
                {
                    existsPreConversionResource = PreConversionResourceExistenceFlag.True;
                }
            }

            // キャッシュから引いたガチャ情報取得
            var gachaDrawInfoModel = GachaCacheRepository.GetGachaDrawInfoModel();
            GachaCacheRepository.ClearGachaDrawType();

            var gachaDrawableFlag = CanDraw(gachaDrawInfoModel);


            var isInAppReviewDisplay = ShouldDisplayInAppReview(resultModels, gachaDrawInfoModel);
            if (isInAppReviewDisplay)
            {
                InAppReviewPreferenceRepository.SetIsAppReviewDisplayedAfterGachaUrDrawn(isInAppReviewDisplay);
            }

            // ステップアップガシャのおまけ情報を取得して消去
            var stepRewardModels = GachaCacheRepository.GetStepRewardModels();
            GachaCacheRepository.ClearStepRewardModels();

            // ステップアップおまけをGachaResultModelからCommonReceiveResourceModelに変換
            var stepRewardCommonReceiveModels = CreateCommonReceiveResourceModels(stepRewardModels);

            var useCaseModel = new GachaResultUseCaseModel(
                    gachaDrawInfoModel,
                    gachaDrawableFlag,
                    resultModels,
                    convertedModels,
                    avatarModels,
                    existsPreConversionResource,
                    isInAppReviewDisplay,
                    stepRewardCommonReceiveModels);
            return useCaseModel;
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveResourceModels(
            IReadOnlyList<GachaResultModel> gachaResultModels)
        {
            return gachaResultModels
                .Select(model =>
                    new CommonReceiveResourceModel(
                        model.RewardModel.UnreceivedRewardReasonType,
                        PlayerResourceModelFactory.Create(
                            model.RewardModel.ResourceType,
                            model.RewardModel.ResourceId,
                            model.RewardModel.Amount),
                        PlayerResourceModelFactory.Create(model.RewardModel.PreConversionResource)))
                .ToList();
        }

        DrawableFlag CanDraw(GachaDrawInfoModel gachaDrawInfoModel)
        {
            if(gachaDrawInfoModel.IsEmpty()) return DrawableFlag.False;

            // チュートリアルガシャは必ず引き直し可能
            if (gachaDrawInfoModel.GachaType == GachaType.Tutorial) return DrawableFlag.True;
            
            // ステップアップガチャは必ずトップ画面で引くため、結果画面からは引けない
            if (gachaDrawInfoModel.GachaType == GachaType.Stepup) return DrawableFlag.False;

            var oprGachaModel = OprGachaRepository.GetOprGachaModelFirstOrDefaultById(gachaDrawInfoModel.GachaId);
            
            // oprGachaModelがnullの場合は引けない
            if (oprGachaModel == null) return DrawableFlag.False;
            
            var gachaDrawableFlag = DrawableFlag.True;

            var userGachaModel = GameRepository.GetGameFetchOther().UserGachaModels.FirstOrDefault(
                model => model.OprGachaId == oprGachaModel.Id,
                UserGachaModel.CreateById(oprGachaModel.Id));

            var useResourceModels = GetUseResourceModels(oprGachaModel, gachaDrawInfoModel.GachaDrawType, userGachaModel);

            var highestUseResourceModel = GachaContentCalculator.GetHighestPriorityUseResourceModel(
                useResourceModels,
                GameRepository.GetGameFetch(),
                GameRepository.GetGameFetchOther(),
                SystemInfoProvider.GetApplicationSystemInfo().PlatformId);
            
            // highestUseResourceModelがnullの場合は引けない
            if (highestUseResourceModel == null) return DrawableFlag.False;

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

        IReadOnlyList<OprGachaUseResourceModel> GetUseResourceModels(
            OprGachaModel oprGachaModel,
            GachaDrawType gachaDrawType,
            UserGachaModel userGachaModel)
        {
            // ステップアップガチャの場合は現在のステップから取得
            if (oprGachaModel.GachaType == GachaType.Stepup)
            {
                // UserGachaModelから現在のステップ番号を取得
                var stepNumber = userGachaModel.CurrentStepNumber.Value == 0
                    ? new StepUpGachaStepNumber(1) // デフォルトは1ステップ目
                    : new StepUpGachaStepNumber(userGachaModel.CurrentStepNumber.Value);
                
                var stepModel = OprStepUpGachaStepRepository.GetOprStepUpGachaStepModelFirstOrDefault(oprGachaModel.Id, stepNumber);
                
                if (stepModel.IsEmpty())
                {
                    return new List<OprGachaUseResourceModel>();
                }
                
                // 初回無料判定: IsFirstFreeがtrueで、かつ1ループ目(CurrentLoopCount == 0)の場合
                var isFirstLoopCycle = userGachaModel.CurrentLoopCount.Value == 0;
                var isFirstFree = stepModel.IsFirstFree && isFirstLoopCycle;
                
                var costType = isFirstFree ? CostType.Free : stepModel.CostType;
                var costAmount = isFirstFree ? CostAmount.Zero : stepModel.CostAmount;
                
                // ステップモデルからOprGachaUseResourceModel相当の情報を作成
                var stepUseResourceModel = new OprGachaUseResourceModel(
                    stepModel.OprGachaId,
                    costType,
                    stepModel.MstCostId,
                    costAmount,
                    stepModel.DrawCount,
                    GachaCostPriority.Empty // ステップアップガチャでは優先度は不要
                );
                
                return new List<OprGachaUseResourceModel> { stepUseResourceModel };
            }
            
            // 通常のガチャの処理
            if (gachaDrawType == GachaDrawType.Single)
            {
                return OprGachaUseResourceRepository.FindByGachaId(oprGachaModel.Id)
                    .Where(model => model.GachaDrawCount?.Value == 1)
                    .ToList();
            }
            else if (gachaDrawType == GachaDrawType.Multi)
            {
                return OprGachaUseResourceRepository.FindByGachaId(oprGachaModel.Id)
                    .Where(model => model.GachaDrawCount?.Value > 1)
                    .ToList();
            }

            return new List<OprGachaUseResourceModel>();
        }
    }
}
