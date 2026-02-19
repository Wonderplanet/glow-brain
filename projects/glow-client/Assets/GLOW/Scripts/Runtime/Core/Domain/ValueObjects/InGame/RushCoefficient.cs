namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record RushCoefficient(decimal Value)
    {
        public static RushCoefficient Empty { get; } = new(0);

        public static RushCoefficient Default { get; } = new(1);


    }
}
