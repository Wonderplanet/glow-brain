namespace GLOW.Modules.Tutorial.Domain.ValueObject
{
    public record TutorialSequenceAssetKey(string Value)
    {
        public static TutorialSequenceAssetKey Empty { get; } = new TutorialSequenceAssetKey(string.Empty);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
