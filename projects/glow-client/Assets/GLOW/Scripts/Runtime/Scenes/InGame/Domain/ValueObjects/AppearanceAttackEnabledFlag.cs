namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record AppearanceAttackEnabledFlag(bool Value)
    {
        public static AppearanceAttackEnabledFlag True { get; } = new (true);
        public static AppearanceAttackEnabledFlag False { get; } = new (false);
        public static implicit operator bool(AppearanceAttackEnabledFlag flag) => flag.Value;
    }
}