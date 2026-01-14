namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record AdChallengeFlag(bool Value)
    {
        public static AdChallengeFlag True { get; } = new(true);
        public static AdChallengeFlag False { get; } = new(false);
        
        public static implicit operator bool(AdChallengeFlag flag) => flag.Value;
    }
}