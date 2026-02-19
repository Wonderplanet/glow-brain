using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models.LogModel;

namespace GLOW.Scenes.AdventBattle.Domain.Factory
{
    public interface IPartyStatusModelFactory
    {
        public PartyStatusModel CreatePartyStatusModel(
            UserUnitModel userUnitModel,
            InGameType inGameType,
            MasterDataId questId,
            EventBonusGroupId eventBonusGroupId,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels);

        public PartyStatusModel CreatePartyStatusModel(
            PvpUnitModel pvpUnitModel,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels);
    }
}
