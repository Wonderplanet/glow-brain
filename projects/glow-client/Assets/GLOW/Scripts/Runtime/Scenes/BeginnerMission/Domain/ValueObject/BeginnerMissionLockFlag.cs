namespace GLOW.Scenes.BeginnerMission.Domain.ValueObject
{
    public record BeginnerMissionLockFlag(bool Value)
    {
        public static BeginnerMissionLockFlag True { get; } = new(true);
        public static BeginnerMissionLockFlag False { get; } = new(false);
        
        public static implicit operator bool(BeginnerMissionLockFlag value) => value.Value;
    }
}