using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IBattleEndCheckProcess
    {
        BattleEndCheckProcessResult UpdateBattleEnd(
            BattleEndModel battleEndModel,
            StageTimeModel stageTime,
            HP playerOutpostHP,
            HP enemyOutpostHP,
            HP defenseTargetHP,
            IReadOnlyList<CharacterUnitModel> deadUnits,
            DefeatEnemyCount defeatEnemyCount,
            IReadOnlyDictionary<MasterDataId, DefeatEnemyCount> defeatEnemyCountDictionary,
            BattleGiveUpFlag isGiveUp);
    }
}
