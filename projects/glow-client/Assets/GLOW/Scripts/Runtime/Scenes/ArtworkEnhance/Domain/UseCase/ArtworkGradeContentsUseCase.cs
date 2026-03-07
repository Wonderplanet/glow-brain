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
    public class ArtworkGradeContentsUseCase
    {
        [Inject] IMstArtworkGradeUpDataRepository ArtworkGradeUpDataRepository { get; }
        [Inject] IMstArtworkDataRepository ArtworkDataRepository { get; }
        [Inject] IMstItemDataRepository ItemDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstConfigRepository ConfigRepository { get; }

        public ArtworkGradeContentsUseCaseModel GetArtworkRankUpContentsUseCaseModel(MasterDataId mstArtworkId)
        {
            var artwork = ArtworkDataRepository.GetArtwork(mstArtworkId);
            var artworkGradeUpModels =
                ArtworkGradeUpDataRepository.GetMstArtworkGradeUps(artwork.Rarity, artwork.MstSeriesId, artwork.Id);

            var userArtwork = GameRepository.GetGameFetchOther().UserArtworkModels
                .FirstOrDefault(artwork => artwork.MstArtworkId == mstArtworkId, UserArtworkModel.Empty);

            var maxGradeLevel = ConfigRepository.GetConfig(MstConfigKey.ArtworkGradeCap).Value.ToInt();

            var gradeContents = artworkGradeUpModels
                .Select(model => CreateCellModels(artwork.Name, model, userArtwork.Grade, maxGradeLevel))
                .OrderBy(model => model.IsGradeReleased.Value)
                .ThenBy(model => model.TargetGradeLevel.Value)
                .ToList();

            return new ArtworkGradeContentsUseCaseModel(gradeContents);
        }

        ArtworkGradeContentCellUseCaseModel CreateCellModels(
            ArtworkName artworkName,
            MstArtworkGradeUpModel model,
            ArtworkGradeLevel currentGradeLevel,
            int maxGradeLevel)
        {
            var costItems = CreateGradeUpCostItems(model.GradeUpCostModels);

            var maxGradeFlag = model.GradeLevel >= maxGradeLevel;

            var isReleased = currentGradeLevel >= model.GradeLevel
                ? ArtworkGradeReleasedFlag.True
                : ArtworkGradeReleasedFlag.False;

            return new ArtworkGradeContentCellUseCaseModel(
                artworkName,
                costItems,
                ArtworkGradeLevel.GetRequiredGradeLevel(model.GradeLevel),
                model.GradeLevel,
                isReleased,
                new ArtworkGradeMaxLimitFlag(maxGradeFlag)
            );
        }

        IReadOnlyList<PlayerResourceModel> CreateGradeUpCostItems(IReadOnlyList<ArtworkGradeUpCostModel> costItems)
        {
            var resourceIds = costItems
                .Select(cost => cost.ResourceId)
                .Distinct()
                .ToList();
            var itemDataList = ItemDataRepository.GetItems()
                .Where(item => resourceIds.Contains(item.Id))
                .ToList();

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
