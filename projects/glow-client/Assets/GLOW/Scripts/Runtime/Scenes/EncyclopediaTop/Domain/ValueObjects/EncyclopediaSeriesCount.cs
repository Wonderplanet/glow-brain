namespace GLOW.Scenes.EncyclopediaTop.Domain.ValueObjects
{
    public record EncyclopediaSeriesCount(int Value)
    {
        public static EncyclopediaSeriesCount Empty { get; } = new (0);

        public override string ToString()
        {
            return Value.ToString("D2");
        }
    }
}
