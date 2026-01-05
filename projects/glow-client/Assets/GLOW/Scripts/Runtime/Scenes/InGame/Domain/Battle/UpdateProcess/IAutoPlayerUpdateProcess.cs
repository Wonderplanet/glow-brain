using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IAutoPlayerUpdateProcess
    {
        AutoPlayerUpdateProcessResult UpdateAutoPlayer(
            IReadOnlyList<DeckUnitModel> deckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            IReadOnlyList<CharacterUnitModel> units,
            IReadOnlyList<SpecialUnitModel> specialUnits,
            IReadOnlyList<CharacterUnitModel> deadUnits,
            IReadOnlyList<GimmickObjectToEnemyTransformationModel> gimmickObjectToEnemyTransformationModels,
            DefeatEnemyCount totalDeadEnemyCount,
            OutpostModel playerOutpost,
            OutpostModel enemyOutpost,
            UnitSummonQueueModel unitSummonQueue,
            BossSummonQueueModel bossSummonQueue,
            DeckUnitSummonQueueModel deckUnitSummonQueue,
            SpecialUnitSummonQueueModel specialUnitSummonQueue,
            SpecialUnitSummonInfoModel specialUnitSummonInfo,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel mstPage,
            BattlePointModel battlePoint,
            BattlePointModel pvpOpponentBattlePoint,
            StageTimeModel stageTime,
            RushModel pvpOpponentRushModel,
            TickCount tickCount);
    }
}
