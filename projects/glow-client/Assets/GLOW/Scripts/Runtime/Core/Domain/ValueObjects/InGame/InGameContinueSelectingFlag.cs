namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record InGameContinueSelectingFlag(bool Value)
    {
        public static InGameContinueSelectingFlag True { get; } = new(true);
        public static InGameContinueSelectingFlag False { get; } = new(false);
    
        public static implicit operator bool(InGameContinueSelectingFlag flag) => flag.Value;
    }
}