using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models.LogModel;

namespace GLOW.Scenes.AdventBattle.Domain.Factory
{
    public interface IPvpInGameEndBattleLogModelFactory
    {
        public PvpInGameEndBattleLogModel CreateInGameEndBattleLogModel(
            IReadOnlyList<UserUnitModel> userUnitModels,
            StageClearTime currentTickCount,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels);
    }
}
