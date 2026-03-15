namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record InGameTutorialIntroductionFlag(bool Value)
    {
        public static InGameTutorialIntroductionFlag True { get; } = new InGameTutorialIntroductionFlag(true);
        public static InGameTutorialIntroductionFlag False { get; } = new InGameTutorialIntroductionFlag(false);
        
        public static implicit operator bool(InGameTutorialIntroductionFlag flag) => flag.Value;
    }
}