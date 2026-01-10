using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IBossSummonQueueUpdateProcess
    {
        BossSummonQueueUpdateProcessResult UpdateBossSummonQueue(
            IReadOnlyList<CharacterUnitModel> units,
            BossSummonQueueModel bossSummonQueue,
            BossAppearancePauseModel bossAppearancePause,
            IReadOnlyList<IAttackModel> attacks,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            TickCount tickCount);
    }
}
