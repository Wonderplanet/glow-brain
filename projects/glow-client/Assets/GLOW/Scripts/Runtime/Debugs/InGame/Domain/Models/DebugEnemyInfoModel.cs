#if GLOW_INGAME_DEBUG
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Debugs.InGame.Domain.ValueObjects;

namespace GLOW.Debugs.InGame.Domain.Models
{
    public record DebugEnemyInfoModel(
        DebugSummonTargetId SummonTargetId,
        CharacterName EnemyName,
        CharacterUnitKind UnitKind
    )
    {
        public static readonly DebugEnemyInfoModel Empty = new (
            DebugSummonTargetId.Empty,
            CharacterName.Empty,
            CharacterUnitKind.Normal
        );
    }
}
#endif // GLOW_INGAME_DEBUG

