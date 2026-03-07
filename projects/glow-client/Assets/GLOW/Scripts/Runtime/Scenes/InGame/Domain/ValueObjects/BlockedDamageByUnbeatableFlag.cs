namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record BlockedDamageByUnbeatableFlag(bool Value)
    {
        public static BlockedDamageByUnbeatableFlag True { get; } = new(true);
        public static BlockedDamageByUnbeatableFlag False { get; } = new(false);

        public static implicit operator bool(BlockedDamageByUnbeatableFlag flag) => flag.Value;
    }
}