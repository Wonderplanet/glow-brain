using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Encyclopedia;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Domain.UseCase
{
    public class ArtworkEnhanceUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstSeriesDataRepository SeriesDataRepository { get; }
        [Inject] IMstArtworkDataRepository ArtworkDataRepository { get; }
        [Inject] IMstArtworkGradeUpDataRepository ArtworkGradeUpDataRepository { get; }
        [Inject] IMstItemDataRepository ItemDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMstArtworkEffectDescriptionDataRepository ArtworkEffectDescriptionDataRepository { get; }
        [Inject] IMstArtworkAcquisitionRouteRepository MstArtworkAcquisitionRouteRepository { get; }
        [Inject] IMstArtworkFragmentDataRepository MstArtworkFragmentDataRepository { get; }
        [Inject] IMstConfigRepository ConfigRepository { get; }

        public ArtworkEnhanceUseCaseModel CreateArtworkEnhanceUseCaseModel(MasterDataId mstArtworkId)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userArtworkModels = gameFetchOther.UserArtworkModels;
            var userArtwork = userArtworkModels
                .FirstOrDefault(model => model.MstArtworkId == mstArtworkId, UserArtworkModel.Empty);

            // シリーズのアセットキーを取得
            var artwork = ArtworkDataRepository.GetArtwork(mstArtworkId);
            var defaultSeries = ConfigRepository.GetConfig(MstConfigKey.DefaultSeriesLogoId).Value;
            var seriesAssetKey = defaultSeries.ToSeriesAssetKey();
            if (artwork.MstSeriesId != defaultSeries.ToMasterDataId())
            {
                seriesAssetKey = SeriesDataRepository.GetMstSeriesModel(artwork.MstSeriesId).SeriesAssetKey;
            }

            // 原画が完成しているかどうか
            var artworkCompleted = userArtwork.IsEmpty()
                ? ArtworkCompletedFlag.False
                : ArtworkCompletedFlag.True;

            // 現在のグレードを取得(未所持の場合はグレード１として扱う)
            var artworkGrade = userArtwork.Grade.IsEmpty()
                ? new ArtworkGradeLevel(1)
                : userArtwork.Grade;

            // 原画の効果詳細を取得
            var effectDescriptions =
                ArtworkEffectDescriptionDataRepository.GetArtworkEffectDescriptionFirstOrDefault(artwork.Id);
            var effectDescription = effectDescriptions.Descriptions
                .FirstOrDefault(desc => desc.GradeLevel == artworkGrade,
                    ArtworkEffectDescriptionModel.Empty);

            // 獲得先が存在するか
            var acquisitionRouteExistsFlag = CheckArtworkAcquisitionRouteExists(mstArtworkId);

            var maxGradeLevel = ConfigRepository.GetConfig(MstConfigKey.ArtworkGradeCap).Value.ToInt();
            var isMaxGrade = new ArtworkGradeMaxLimitFlag(artworkGrade >= maxGradeLevel);

            // グレードアップに関する情報を取得
            var artworkGradeUp = ArtworkGradeUpDataRepository
                .GetArtworkGradeUp(artwork.Rarity, artworkGrade, artwork.MstSeriesId, mstArtworkId);

            // 原画が完成してない or 最大グレードに達している場合 or グレードアップ情報が存在しない場合
            // グレードアップ情報を含めないで返す
            if (artworkCompleted == ArtworkCompletedFlag.False
                || isMaxGrade
                || artworkGradeUp.IsEmpty())
            {
                return CreateArtworkEnhanceModelExcludingGradeUp(
                    mstArtworkId,
                    artwork,
                    seriesAssetKey,
                    artworkCompleted,
                    acquisitionRouteExistsFlag,
                    effectDescription.Description,
                    artworkGrade);
            }

            // グレードアップに必要なアイテムを取得
            var artworkGradeUpCostItems = CreateGradeUpCostItems(artworkGradeUp.GradeUpCostModels);

            // 各アイテムを所持しているかどうかのフラグ
            var userItemModels = gameFetchOther.UserItemModels;
            var gradeUpItemEnoughFlags =
                CreateGradeUpItemEnoughFlags(artworkGradeUpCostItems, userItemModels);

            // グレードアップ可能かどうか
            var artworkGradeUpAvailable = gradeUpItemEnoughFlags
                .All(x => x == ArtworkGradeUpItemEnoughFlag.True)
                ? ArtworkGradeUpAvailableFlag.True
                : ArtworkGradeUpAvailableFlag.False;

            return new ArtworkEnhanceUseCaseModel(
                mstArtworkId,
                artwork.Name,
                artwork.Rarity,
                artworkGrade,
                seriesAssetKey,
                artworkCompleted,
                artworkGradeUpAvailable,
                isMaxGrade,
                acquisitionRouteExistsFlag,
                effectDescription.Description,
                artwork.Description,
                artworkGradeUpCostItems,
                gradeUpItemEnoughFlags);
        }

        IReadOnlyList<ArtworkGradeUpItemEnoughFlag> CreateGradeUpItemEnoughFlags(
            IReadOnlyList<PlayerResourceModel> gradeUpCostItems,
            IReadOnlyList<UserItemModel> userItemModels)
        {
            var flags = gradeUpCostItems
                .GroupJoin(
                    userItemModels,
                    cost => cost.Id,
                    item => item.MstItemId,
                    (cost, items) => new { Cost = cost, Item = items.FirstOrDefault() })
                .Select(x =>
                {
                    var hasEnough = x.Item != null && x.Item.Amount.Value >= x.Cost.Amount.Value;
                    return hasEnough
                        ? ArtworkGradeUpItemEnoughFlag.True
                        : ArtworkGradeUpItemEnoughFlag.False;
                })
                .ToList();

            return flags;
        }

        IReadOnlyList<PlayerResourceModel> CreateGradeUpCostItems(IReadOnlyList<ArtworkGradeUpCostModel> costItems)
        {
            var resourceIds = costItems.
                Select(cost => cost.ResourceId).Distinct().ToList();
            var itemDataList = ItemDataRepository.GetItems().
                Where(item => resourceIds.Contains(item.Id)).ToList();

            var models = costItems
                .GroupJoin(
                    itemDataList,
                    cost => cost.ResourceId,
                    item => item.Id,
                    (cost, items) => new { Cost = cost, Item = items.FirstOrDefault() })
                .Where(x => x.Item != null)
                .Select(x => PlayerResourceModelFactory.Create(
                    ResourceType.Item,
                    x.Item.Id,
                    new PlayerResourceAmount(x.Cost.ResourceAmount.Value)))
                .ToList();

            return models;
        }

        ArtworkEnhanceUseCaseModel CreateArtworkEnhanceModelExcludingGradeUp(
            MasterDataId mstArtworkId,
            MstArtworkModel artwork,
            SeriesAssetKey seriesAssetKey,
            ArtworkCompletedFlag artworkCompleted,
            ArtworkAcquisitionRouteExistsFlag acquisitionRouteExistsFlag,
            ArtworkEffectDescription effectDescription,
            ArtworkGradeLevel artworkGradeLevel)
        {
            return ArtworkEnhanceUseCaseModel.CreateDefault() with
            {
                MstArtworkId = mstArtworkId,
                Name = artwork.Name,
                Rarity = artwork.Rarity,
                SeriesLogoImageKey = seriesAssetKey,
                ArtworkCompletedFlag = artworkCompleted,
                AcquisitionRouteExistsFlag = acquisitionRouteExistsFlag,
                EffectDescription = effectDescription,
                ArtworkDescription = artwork.Description,
                GradeMaxLimitFlag = ArtworkGradeMaxLimitFlag.True,
                GradeLevel = artworkGradeLevel
            };
        }

        ArtworkAcquisitionRouteExistsFlag CheckArtworkAcquisitionRouteExists(MasterDataId mstArtworkId)
        {
            // 原画の獲得先が存在するかどうかをチェック
            var acquisitionRouteExists = !MstArtworkAcquisitionRouteRepository.GetArtworkAcquisitionRouteFirstOrDefault(mstArtworkId).IsEmpty()
                || MstArtworkFragmentDataRepository.GetArtworkFragments(mstArtworkId).Any();

            return new ArtworkAcquisitionRouteExistsFlag(acquisitionRouteExists);
        }
    }
}
