namespace GLOW.Core.Domain.ValueObjects
{
    public record EncyclopediaUnitGrade(int Value)
    {
        public static EncyclopediaUnitGrade Empty { get; } = new(0);

        public override string ToString()
        {
            return Value.ToString();
        }
    }
}
