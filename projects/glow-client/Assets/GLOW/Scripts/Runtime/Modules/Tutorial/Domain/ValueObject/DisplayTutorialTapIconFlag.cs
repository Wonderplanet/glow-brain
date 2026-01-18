namespace GLOW.Modules.Tutorial.Domain.ValueObject
{
    public record DisplayTutorialTapIconFlag(bool Value)
    {
        public static DisplayTutorialTapIconFlag True { get; } = new DisplayTutorialTapIconFlag(true);
        public static DisplayTutorialTapIconFlag False { get; } = new DisplayTutorialTapIconFlag(false);

        public static implicit operator bool(DisplayTutorialTapIconFlag displayTutorialTapIconFlag) => displayTutorialTapIconFlag.Value;
    }
}
