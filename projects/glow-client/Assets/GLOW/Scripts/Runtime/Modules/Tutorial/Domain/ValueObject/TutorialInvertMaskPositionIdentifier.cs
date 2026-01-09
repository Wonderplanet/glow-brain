namespace GLOW.Modules.Tutorial.Domain.ValueObject
{
    public record TutorialInvertMaskPositionIdentifier(string Value)
    {
        public static TutorialInvertMaskPositionIdentifier Empty { get; } = new TutorialInvertMaskPositionIdentifier(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
