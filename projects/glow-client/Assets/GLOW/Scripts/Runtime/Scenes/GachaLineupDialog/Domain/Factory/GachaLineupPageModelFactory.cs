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
using GLOW.Scenes.GachaLineupDialog.Domain.Models;
using GLOW.Scenes.GachaRatio.Domain.Model;
using Zenject;

namespace GLOW.Scenes.GachaLineupDialog.Domain.Factory
{
    public class GachaLineupPageModelFactory : IGachaLineupPageModelFactory
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        GachaLineupPageModel IGachaLineupPageModelFactory.Create(GachaPrizePageModel prizePageModel)
        {
            return CreatePageModel(prizePageModel);
        }

        GachaLineupPageModel CreatePageModel(GachaPrizePageModel prizePageModel)
        {
            if (prizePageModel.IsEmpty() || prizePageModel.RarityProbabilities.Count == 0) return GachaLineupPageModel.Empty;

            return new GachaLineupPageModel(
                CreateLineupListModel(prizePageModel.ProbabilityGroupModels, true),
                CreateLineupListModel(prizePageModel.ProbabilityGroupModels, false));
        }

        GachaLineupListModel CreateLineupListModel(IReadOnlyList<GachaProbabilityGroupModel> groupModels, bool isPickup)
        {
            var rLineupModel = CreateLineupModel(groupModels.FirstOrDefault(model => model.Rarity == Rarity.R, new GachaProbabilityGroupModel(Rarity.R, new List<GachaPrizeModel>())), isPickup);
            var srLineupModel = CreateLineupModel(groupModels.FirstOrDefault(model => model.Rarity == Rarity.SR, new GachaProbabilityGroupModel(Rarity.SR, new List<GachaPrizeModel>())), isPickup);
            var ssrLineupModel = CreateLineupModel(groupModels.FirstOrDefault(model => model.Rarity == Rarity.SSR, new GachaProbabilityGroupModel(Rarity.SSR, new List<GachaPrizeModel>())), isPickup);
            var urLineupModel = CreateLineupModel(groupModels.FirstOrDefault(model => model.Rarity == Rarity.UR, new GachaProbabilityGroupModel(Rarity.UR, new List<GachaPrizeModel>())), isPickup);

            return new GachaLineupListModel(urLineupModel, ssrLineupModel, srLineupModel, rLineupModel);
        }

        GachaLineupCellListModel CreateLineupModel(GachaProbabilityGroupModel groupModel, bool isPickup)
        {
            if (groupModel.Prizes.Count == 0)
            {
                return new GachaLineupCellListModel(
                    new RatioProbabilityAmount(groupModel.Rarity, 0),
                    new List<GachaLineupCellModel>());
            }

            List<GachaLineupCellModel> lineupCellModels;
            if (isPickup)
            {
                lineupCellModels = groupModel.Prizes
                    .Where(prize => prize.Pickup)
                    .Select((prize, index) => CreateLineupCellModel(prize, index))
                    .ToList();
            }
            else
            {
                lineupCellModels = groupModel.Prizes
                    .Select((prize, index) => CreateLineupCellModel(prize, index))
                    .ToList();
            }

            return new GachaLineupCellListModel(new RatioProbabilityAmount(groupModel.Rarity, lineupCellModels.Count), lineupCellModels);
        }

        GachaLineupCellModel CreateLineupCellModel(GachaPrizeModel prizeModel, int index)
        {
            var characterName = CharacterName.Empty;
            if (prizeModel.ResourceType == ResourceType.Unit)
            {
                characterName = MstCharacterDataRepository.GetCharacter(prizeModel.MasterDataId).Name;
            }

            var resourceModel = PlayerResourceModelFactory.Create(prizeModel.ResourceType, prizeModel.MasterDataId, new PlayerResourceAmount(prizeModel.ResourceAmount));

            return new GachaLineupCellModel(
                new GachaRatioResourceModel(resourceModel.Type, resourceModel.Id, resourceModel.Amount),
                resourceModel,
                characterName,
                resourceModel.Name,
                new NumberParity(index)
            );
        }
    }
}