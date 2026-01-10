namespace GLOW.Modules.TutorialTapIcon.Presentation.ValueObject
{
    public record ReverseFlag(bool Value)
    {
        public static ReverseFlag False { get; } = new ReverseFlag(false);
        public static ReverseFlag True { get; } = new ReverseFlag(true);

        public static implicit operator bool(ReverseFlag flag) => flag.Value;
    }
}
