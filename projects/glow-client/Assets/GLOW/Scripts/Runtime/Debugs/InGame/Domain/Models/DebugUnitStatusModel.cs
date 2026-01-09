#if GLOW_INGAME_DEBUG
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Debugs.InGame.Domain.Models
{
    public record DebugUnitStatusModel(
        HP MaxHp,
        HP Hp,
        AttackPower AttackPower)
    {
        public static readonly DebugUnitStatusModel Empty = new(HP.Empty, HP.Empty, AttackPower.Empty);
    }
}
#endif // GLOW_INGAME_DEBUG

