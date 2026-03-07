namespace GLOW.Core.Domain.ValueObjects
{
    public record UnreceivedByResourceOverflowDiscardedFlag(bool Value)
    {
        public static UnreceivedByResourceOverflowDiscardedFlag True { get; } = 
            new UnreceivedByResourceOverflowDiscardedFlag(true);
        
        public static UnreceivedByResourceOverflowDiscardedFlag False { get; } = 
            new UnreceivedByResourceOverflowDiscardedFlag(false);

        public static implicit operator bool(UnreceivedByResourceOverflowDiscardedFlag flag) => flag.Value;
    }
}