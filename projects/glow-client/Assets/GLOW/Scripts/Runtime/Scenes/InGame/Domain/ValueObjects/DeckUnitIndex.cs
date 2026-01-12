namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record DeckUnitIndex(int Value)
    {
        public static DeckUnitIndex Empty { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
