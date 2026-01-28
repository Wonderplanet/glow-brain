namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record StageFlavorText(string Value)
    {
        public static StageFlavorText Empty { get; } = new (string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}