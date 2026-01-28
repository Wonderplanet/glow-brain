namespace GLOW.Modules.TutorialTapIcon.Presentation.ValueObject
{
    public record TutorialTapIconPosition(float X, float Y)
    {
        public static TutorialTapIconPosition Empty { get; } = new TutorialTapIconPosition(0, 0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
