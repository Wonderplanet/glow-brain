namespace GLOW.Scenes.Home.Domain.ValueObjects
{
    public record DisplayAtLoginFlag(bool Value)
    {
        public static DisplayAtLoginFlag True { get; } = new DisplayAtLoginFlag(true);
        public static DisplayAtLoginFlag False { get; } = new DisplayAtLoginFlag(false);
        
        public static implicit operator bool(DisplayAtLoginFlag flag) => flag.Value;
        
    }
}