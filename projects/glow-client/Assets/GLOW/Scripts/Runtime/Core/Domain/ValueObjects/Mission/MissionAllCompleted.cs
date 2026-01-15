namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record MissionAllCompleted(bool Value)
    {
        public static MissionAllCompleted DefaultSetting { get; } = new(false);
    }
}