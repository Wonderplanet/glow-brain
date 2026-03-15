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
    public class ArtworkUpGradeConfirmUseCase
    {
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMstArtworkDataRepository ArtworkDataRepository { get; }
        [Inject] IMstArtworkGradeUpDataRepository ArtworkGradeUpDataRepository { get; }
        [Inject] IMstItemDataRepository ItemDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstArtworkEffectDescriptionDataRepository ArtworkEffectDescriptionDataRepository { get; }
        [Inject] IMstConfigRepository ConfigRepository { get; }

        public ArtworkUpGradeConfirmUseCaseModel GetArtworkEnhanceConfirmUseCaseModel(MasterDataId mstArtworkId)
        {
            var artwork = ArtworkDataRepository.GetArtwork(mstArtworkId);
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userArtwork = gameFetchOther.UserArtworkModels
                .FirstOrDefault(model => model.MstArtworkId == mstArtworkId, UserArtworkModel.Empty);

            var artworkName = ArtworkDataRepository.GetArtwork(mstArtworkId).Name;

            // グレードアップに関する情報を取得
            var artworkGradeUpModel = ArtworkGradeUpDataRepository.GetArtworkGradeUp(
                artwork.Rarity,
                userArtwork.Grade,
                artwork.MstSeriesId,
                artwork.Id);

            // グレードアップに必要なアイテムを取得
            var requiredEnhanceItems =
                CreateRequiredEnhanceItemModel(artworkGradeUpModel.GradeUpCostModels, gameFetchOther);

            // 次のグレードの原画効果詳細を取得
            var artworkEffectDescriptions =
                ArtworkEffectDescriptionDataRepository.GetArtworkEffectDescriptionFirstOrDefault(artwork.Id);
            var artworkEffectDescription = artworkEffectDescriptions.Descriptions
                .FirstOrDefault(x => x.GradeLevel == artworkGradeUpModel.GradeLevel,
                    ArtworkEffectDescriptionModel.Empty);

            // グレードが最大か取得
            var artworkGradeCap = ConfigRepository.GetConfig(MstConfigKey.ArtworkGradeCap).Value.ToInt();
            var isGradeCap = artworkGradeUpModel.GradeLevel >= artworkGradeCap;

            return new ArtworkUpGradeConfirmUseCaseModel(
                artworkName,
                requiredEnhanceItems,
                userArtwork.Grade,
                artworkGradeUpModel.GradeLevel,
                artworkEffectDescription.Description,
                new ArtworkGradeMaxLimitFlag(isGradeCap)
                );
        }

        IReadOnlyList<RequiredEnhanceItemUseCaseModel> CreateRequiredEnhanceItemModel(
            IReadOnlyList<ArtworkGradeUpCostModel> gradeUpCostModels,
            GameFetchOtherModel gameFetchOther)
        {
            var costItems = CreateGradeUpCostItems(gradeUpCostModels);
            var possessionAmounts = GetPossessionAmount(costItems, gameFetchOther);
            var consumeAmounts = GetConsumeAmount(costItems);

            return costItems
                .Zip(possessionAmounts, (item, possession) => new { Item = item, Possession = possession })
                .Zip(consumeAmounts, (x, consume) => new RequiredEnhanceItemUseCaseModel(
                    x.Item,
                    x.Possession,
                    consume))
                .ToList();
        }

        IReadOnlyList<ItemAmount> GetConsumeAmount(
            IReadOnlyList<PlayerResourceModel> playerResources)
        {
            return playerResources
                .Select(resource => resource.Amount.ToItemAmount())
                .ToList();
        }

        IReadOnlyList<ItemAmount> GetPossessionAmount(
            IReadOnlyList<PlayerResourceModel> playerResources,
            GameFetchOtherModel gameFetchOther)
        {
            return playerResources
                .GroupJoin(
                    gameFetchOther.UserItemModels,
                    resource => resource.Id,
                    userItem => userItem.MstItemId,
                    (resource, userItem) => userItem.FirstOrDefault()?.Amount ?? ItemAmount.Zero)
                .ToList();
        }

        IReadOnlyList<PlayerResourceModel> CreateGradeUpCostItems(IReadOnlyList<ArtworkGradeUpCostModel> costItems)
        {
            var resourceIds = costItems.Select(cost => cost.ResourceId).Distinct().ToList();
            var itemDataList = ItemDataRepository.GetItems().Where(item => resourceIds.Contains(item.Id)).ToList();

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
    }
}
