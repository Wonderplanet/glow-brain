namespace GLOW.Scenes.UserLevelUp.Domain.ValueObject
{
    public record ExpChangedFlag(bool Value)
    {
        public static ExpChangedFlag True { get; } = new(true);
        public static ExpChangedFlag False { get; } = new(false);
        
        public static implicit operator bool(ExpChangedFlag flag) => flag.Value;
    }
}