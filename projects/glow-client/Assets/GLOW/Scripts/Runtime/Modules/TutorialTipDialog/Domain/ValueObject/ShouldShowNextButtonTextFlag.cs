namespace GLOW.Modules.TutorialTipDialog.Domain.ValueObject
{
    public record ShouldShowNextButtonTextFlag(bool Value)
    {
        public static ShouldShowNextButtonTextFlag True { get; } = new ShouldShowNextButtonTextFlag(true);
        public static ShouldShowNextButtonTextFlag False { get; } = new ShouldShowNextButtonTextFlag(false);

        public static implicit operator bool(ShouldShowNextButtonTextFlag flag) => flag.Value;
    }
}