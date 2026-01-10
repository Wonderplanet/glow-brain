namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record IsDeckComponentLock(bool Value)
    {
        public static IsDeckComponentLock False { get; } = new (false);
        public static IsDeckComponentLock True { get; } = new (true);
    }
}