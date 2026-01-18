namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record MissionClearFrag(bool Value)
    {
        public static MissionClearFrag True { get; } = new MissionClearFrag(true);
        public static MissionClearFrag False { get; } = new MissionClearFrag(false);

        public static implicit operator bool(MissionClearFrag flag) => flag.Value;
    }
}