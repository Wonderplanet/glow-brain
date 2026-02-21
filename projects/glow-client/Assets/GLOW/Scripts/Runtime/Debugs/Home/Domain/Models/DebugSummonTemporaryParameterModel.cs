using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Debugs.Home.Domain.Models
{
    public record DebugSummonTemporaryParameterModel(
        MasterDataId Id,
        UnitAssetKey AssetKey,
        UnitMoveSpeed MoveSpeed,
        AttackRangeParameter AttackRange,
        TickCount NormalAttackDelay,
        TickCount NormalAttackActionDuration,
        List<AttackElement> NormalAttackElements,
        TickCount SpecialAttackDelay,
        TickCount SpecialActionDuration,
        List<AttackElement> SpecialAttackElements,
        AttackComboCycle AttackComboCycle);

}
