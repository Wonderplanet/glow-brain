namespace GLOW.Scenes.ArtworkFragment.Domain.ValueObjects
{
    public record ArtworkFragmentNum(int Value)
    {
        public static ArtworkFragmentNum Empty { get; } = new ArtworkFragmentNum(0);
        public static ArtworkFragmentNum Zero { get; } = new ArtworkFragmentNum(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }
    }
}
