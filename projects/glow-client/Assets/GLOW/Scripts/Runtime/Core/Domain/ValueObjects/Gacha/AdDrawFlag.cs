namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record AdDrawFlag(bool Value)
    {
        public static AdDrawFlag True { get; } = new AdDrawFlag(true);
        public static AdDrawFlag False { get; } = new AdDrawFlag(false);
        
        public static implicit operator bool(AdDrawFlag flag) => flag.Value; 
    }
}