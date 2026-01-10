namespace GLOW.Scenes.Home.Domain.Models
{
    public record BeginnerMissionFinishedFlag(bool Value)
    {
        public static BeginnerMissionFinishedFlag False { get; } = new(false);
        public static BeginnerMissionFinishedFlag True { get; } = new(true);
        
        public static implicit operator bool(BeginnerMissionFinishedFlag flag) => flag.Value;
        
    };
}