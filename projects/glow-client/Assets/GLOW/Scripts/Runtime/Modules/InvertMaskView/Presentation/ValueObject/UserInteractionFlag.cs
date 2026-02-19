namespace GLOW.Modules.InvertMaskView.Presentation.ValueObject
{
    public record UserInteractionFlag(bool Value)
    {
        public static UserInteractionFlag True { get; } = new UserInteractionFlag(true);
        public static UserInteractionFlag False { get; } = new UserInteractionFlag(false);

        public static implicit operator bool(UserInteractionFlag flag) => flag.Value;
    }
}
