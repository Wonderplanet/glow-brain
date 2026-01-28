namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record RetryAvailableFlag(bool Value)
    {
        public static RetryAvailableFlag True { get; } = new(true);
        public static RetryAvailableFlag False { get; } = new(false);
        
        public static implicit operator bool(RetryAvailableFlag flag) => flag.Value;
    }
}