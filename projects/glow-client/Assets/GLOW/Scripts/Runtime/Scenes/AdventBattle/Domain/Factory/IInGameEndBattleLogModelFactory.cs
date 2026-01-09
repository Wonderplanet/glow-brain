using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.LogModel;

namespace GLOW.Scenes.AdventBattle.Domain.Factory
{
    public interface IInGameEndBattleLogModelFactory
    {
        public InGameEndBattleLogModel CreateInGameEndBattleLogModel(
            IReadOnlyList<UserUnitModel> userUnitModels,
            StageClearTime currentTickCount);
    }
}
