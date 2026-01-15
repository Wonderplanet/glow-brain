using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface ICharacterUnitUpdateProcess
    {
        CharacterUnitUpdateProcessResult UpdateCharacterUnits(
            IReadOnlyList<CharacterUnitModel> characterUnits,
            IReadOnlyList<CharacterUnitModel> deadUnits,
            DefeatEnemyCount totalDeadEnemyCount,
            OutpostModel playerOutpost,
            OutpostModel enemyOutpost,
            DefenseTargetModel defenseTargetModel,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            IReadOnlyList<IAttackModel> attacks,
            MstPageModel mstPage,
            BossAppearancePauseModel bossAppearancePause,
            StageTimeModel stageTime,
            TickCount tickCount);
    }
}
