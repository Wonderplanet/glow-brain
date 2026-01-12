namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record MissionReceivedFlag(bool Value)
    {
        public static MissionReceivedFlag True { get; } = new MissionReceivedFlag(true);
        public static MissionReceivedFlag False { get; } = new MissionReceivedFlag(false);
    
        public static implicit operator bool(MissionReceivedFlag flag) => flag.Value;
    }
}