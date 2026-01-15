using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record InGameSettingModel(
        TickCount SlipDamageInterval,
        TickCount PoisonDamageInterval,
        TickCount BurnDamageInterval,
        TickCount RegenerationInterval,
        TickCount SpecialAttackCoolTime)
    {
        public static InGameSettingModel Default { get; } = new (
            new TickCount(50),
            new TickCount(50),
            new TickCount(50),
            new TickCount(50),
            new TickCount(500));
    }
}
