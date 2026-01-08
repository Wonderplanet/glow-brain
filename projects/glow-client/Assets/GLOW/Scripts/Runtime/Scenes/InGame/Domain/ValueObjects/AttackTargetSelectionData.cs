using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record AttackTargetSelectionData(
        FieldObjectId AttackerId,
        BattleSide AttackerBattleSide,
        AttackTarget AttackTarget,
        AttackTargetType AttackTargetType,
        IReadOnlyList<CharacterColor> TargetColors,
        IReadOnlyList<CharacterUnitRoleType> TargetRoles,
        bool IsDamagedOnly,
        FieldObjectCount MaxTargetCount,
        CoordinateRange FieldCoordRange)
    {
        public static AttackTargetSelectionData Empty { get; } = new(
            FieldObjectId.Empty,
            BattleSide.Player,
            AttackTarget.Foe,
            AttackTargetType.All,
            new List<CharacterColor>(),
            new List<CharacterUnitRoleType>(),
            false,
            FieldObjectCount.Empty,
            CoordinateRange.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
