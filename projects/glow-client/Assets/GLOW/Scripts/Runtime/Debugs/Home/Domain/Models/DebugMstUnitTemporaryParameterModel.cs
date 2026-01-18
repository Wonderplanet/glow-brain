using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Debugs.Home.Domain.Models
{
    public record DebugMstUnitTemporaryParameterModel(
        MasterDataId Id,
        CharacterName Name,
        UnitAssetKey AssetKey,
        UnitMoveSpeed UnitMoveSpeed,
        AttackRangeParameter AttackRange,
        TickCount NormalAttackDelay,
        TickCount NormalAttackActionDuration,
        List<AttackElement> NormalAttackElements,
        TickCount SpecialAttackDelay,
        TickCount SpecialActionDuration,
        List<AttackElement> SpecialAttackElements);
}
