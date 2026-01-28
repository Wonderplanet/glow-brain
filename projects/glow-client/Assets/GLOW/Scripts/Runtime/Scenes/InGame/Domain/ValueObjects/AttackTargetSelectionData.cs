using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
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
        IReadOnlyList<MasterDataId> TargetSeriesIds,
        IReadOnlyList<MasterDataId> TargetCharacterIds,
        bool IsDamagedOnly,
        FieldObjectCount MaxTargetCount,
        CoordinateRange FieldCoordRange)
    {
        public static AttackTargetSelectionData Empty { get; } = new(
            FieldObjectId.Empty,
            BattleSide.Player,
            AttackTarget.Foe,
            AttackTargetType.All,
            Array.Empty<CharacterColor>(),
            Array.Empty<CharacterUnitRoleType>(),
            Array.Empty<MasterDataId>(),
            Array.Empty<MasterDataId>(),
            false,
            FieldObjectCount.Empty,
            CoordinateRange.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
