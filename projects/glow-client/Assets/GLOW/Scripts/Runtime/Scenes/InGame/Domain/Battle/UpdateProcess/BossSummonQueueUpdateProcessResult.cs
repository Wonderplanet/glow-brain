using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record BossSummonQueueUpdateProcessResult(
        CharacterUnitModel SummonedBoss,
        IReadOnlyList<CharacterUnitModel> UpdatedUnits,
        BossSummonQueueModel UpdatedBossSummonQueue,
        BossAppearancePauseModel UpdatedBossAppearancePause,
        IReadOnlyList<IAttackModel> RemovedAttacks,
        IReadOnlyList<IAttackModel> UpdatedAttacks)
    {
        public static BossSummonQueueUpdateProcessResult Empty { get; } = new(
            CharacterUnitModel.Empty,
            new List<CharacterUnitModel>(),
            BossSummonQueueModel.Empty,
            BossAppearancePauseModel.Empty,
            new List<IAttackModel>(),
            new List<IAttackModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
