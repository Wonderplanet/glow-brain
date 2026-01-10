using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Extensions;
using GLOW.Scenes.GachaRatio.Domain.Model;
using Zenject;

namespace GLOW.Scenes.GachaDetailDialog.Domain.UseCases
{
    public class GachaRatioPageModelFactory : IGachaRatioPageModelFactory
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }


        GachaRatioPageModel IGachaRatioPageModelFactory.Create(GachaPrizePageModel prizePageModel)
        {
            return CreatePageModel(prizePageModel);
        }

        GachaRatioPageModel CreatePageModel(GachaPrizePageModel prizePageModel)
        {
            if (prizePageModel.IsEmpty() || prizePageModel.RarityProbabilities.Count == 0) return GachaRatioPageModel.Empty;

            var sampleUrProbability = prizePageModel.RarityProbabilities
                .FirstOrDefault(m => m.Rarity == Rarity.UR, new GachaRarityProbabilityModel(Rarity.UR, 0));
            var sampleSsrProbability = prizePageModel.RarityProbabilities
                .FirstOrDefault(m => m.Rarity == Rarity.SSR, new GachaRarityProbabilityModel(Rarity.SSR, 0));
            var sampleSrProbability = prizePageModel.RarityProbabilities
                .FirstOrDefault(m => m.Rarity == Rarity.SR, new GachaRarityProbabilityModel(Rarity.SR, 0));
            var sampleRProbability = prizePageModel.RarityProbabilities
                .FirstOrDefault(m => m.Rarity == Rarity.R, new GachaRarityProbabilityModel(Rarity.R, 0));

            var rarityRatioModels = new GachaRatioRarityRatioModel
            (
                new GachaRatioRarityRatioItemModel(sampleUrProbability.Rarity, sampleUrProbability.Probability),
                new GachaRatioRarityRatioItemModel(sampleSsrProbability.Rarity, sampleSsrProbability.Probability),
                new GachaRatioRarityRatioItemModel(sampleSrProbability.Rarity, sampleSrProbability.Probability),
                new GachaRatioRarityRatioItemModel(sampleRProbability.Rarity, sampleRProbability.Probability)
            );

            return new GachaRatioPageModel(
                rarityRatioModels,
                CreateLineupListModel(prizePageModel.ProbabilityGroupModels, true),
                CreateLineupListModel(prizePageModel.ProbabilityGroupModels, false));
        }

        GachaRatioLineupListModel CreateLineupListModel(IReadOnlyList<GachaProbabilityGroupModel> groupModels, bool isPickup)
        {
            var rLineupModel = CreateLineupModel(groupModels.FirstOrDefault(model => model.Rarity == Rarity.R, new GachaProbabilityGroupModel(Rarity.R, new List<GachaPrizeModel>())), isPickup);
            var srLineupModel = CreateLineupModel(groupModels.FirstOrDefault(model => model.Rarity == Rarity.SR, new GachaProbabilityGroupModel(Rarity.SR, new List<GachaPrizeModel>())), isPickup);
            var ssrLineupModel = CreateLineupModel(groupModels.FirstOrDefault(model => model.Rarity == Rarity.SSR, new GachaProbabilityGroupModel(Rarity.SSR, new List<GachaPrizeModel>())), isPickup);
            var urLineupModel = CreateLineupModel(groupModels.FirstOrDefault(model => model.Rarity == Rarity.UR, new GachaProbabilityGroupModel(Rarity.SSR, new List<GachaPrizeModel>())), isPickup);

            return new GachaRatioLineupListModel(urLineupModel, ssrLineupModel, srLineupModel, rLineupModel);
        }

        GachaRatioLineupModel CreateLineupModel(GachaProbabilityGroupModel groupModel, bool isPickup)
        {
            if (groupModel.Prizes.Count == 0)
            {
                return new GachaRatioLineupModel(
                    new RatioProbabilityAmount(groupModel.Rarity, 0),
                    new List<GachaRatioLineupCellModel>());
            }

            List<GachaRatioLineupCellModel> lineupCellModels;
            if (isPickup)
            {
                lineupCellModels = groupModel.Prizes
                    .Where(prize => prize.Pickup)
                    .OrderBy(prize => prize.MasterDataId)
                    .Select(CreateLineupCellModel)
                    .ToList();
            }
            else
            {
                // Pickup優先、同じPickup値内でMasterDataId昇順
                lineupCellModels = groupModel.Prizes
                    .OrderByDescending(prize => prize.Pickup)
                    .ThenBy(prize => prize.MasterDataId)
                    .Select(CreateLineupCellModel)
                    .ToList();
            }

            return new GachaRatioLineupModel(new RatioProbabilityAmount(groupModel.Rarity, lineupCellModels.Count), lineupCellModels);
        }

        GachaRatioLineupCellModel CreateLineupCellModel(GachaPrizeModel prizeModel, int index)
        {
            var characterName = CharacterName.Empty;
            if (prizeModel.ResourceType == ResourceType.Unit)
            {
                characterName = MstCharacterDataRepository.GetCharacter(prizeModel.MasterDataId).Name;
            }

            var resourceModel = PlayerResourceModelFactory.Create(prizeModel.ResourceType, prizeModel.MasterDataId, new PlayerResourceAmount(prizeModel.ResourceAmount));

            return new GachaRatioLineupCellModel(
                new GachaRatioResourceModel(resourceModel.Type, resourceModel.Id, resourceModel.Amount),
                resourceModel,
                characterName,
                resourceModel.Name,
                new OutputRatio((decimal)(float)prizeModel.Probability),
                new NumberParity(index)
            );
        }
    }
}
