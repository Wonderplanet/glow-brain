using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record SpecialUnitUpdateProcessResult(
        IReadOnlyList<SpecialUnitModel> UpdatedSpecialUnits,
        IReadOnlyList<IAttackModel> GeneratedAttacks,
        IReadOnlyList<IAttackModel> UpdatedAttacks)
    {
        public static SpecialUnitUpdateProcessResult Empty { get; } = new(
            new List<SpecialUnitModel>(),
            new List<IAttackModel>(),
            new List<IAttackModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
