using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record CharacterUnitUpdateProcessResult(
        IReadOnlyList<CharacterUnitModel> UpdatedUnits,
        IReadOnlyList<IAttackModel> GeneratedAttacks,
        IReadOnlyList<IAttackModel> UpdatedAttacks,
        IReadOnlyList<FieldObjectId> BlockedUnits)
    {
        public static CharacterUnitUpdateProcessResult Empty { get; } = new(
            new List<CharacterUnitModel>(),
            new List<IAttackModel>(),
            new List<IAttackModel>(),
            new List<FieldObjectId>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
