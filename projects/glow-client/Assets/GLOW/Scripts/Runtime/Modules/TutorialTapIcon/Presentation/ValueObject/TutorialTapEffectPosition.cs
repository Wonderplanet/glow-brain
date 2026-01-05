namespace GLOW.Modules.TutorialTapIcon.Presentation.ValueObject
{
    public record TutorialTapEffectPosition(float X, float Y)
    {
        public static TutorialTapEffectPosition Empty { get; } = new TutorialTapEffectPosition(0, 0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
