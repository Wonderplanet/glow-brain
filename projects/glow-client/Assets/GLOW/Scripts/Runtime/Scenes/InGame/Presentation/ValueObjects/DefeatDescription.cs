namespace GLOW.Scenes.InGame.Presentation.ValueObjects
{
    public record DefeatDescription(string Value)
    {
        public static DefeatDescription Empty { get; } = new (string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
