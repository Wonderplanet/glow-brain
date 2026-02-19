namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record BattleStartNoiseAnimationNeedFlag(bool Value)
    {
        public static BattleStartNoiseAnimationNeedFlag True { get; } = new(true);
        public static BattleStartNoiseAnimationNeedFlag False { get; } = new(false);

        public static implicit operator bool(BattleStartNoiseAnimationNeedFlag flag) => flag.Value;
    }
}