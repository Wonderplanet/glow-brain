namespace GLOW.Scenes.BattleResult.Domain.ValueObjects
{
    public record PvpMaxDistanceRatio(float Value)
    {
        public static PvpMaxDistanceRatio Empty { get; } = new(0f);
        public static PvpMaxDistanceRatio One { get; } = new(1f);

        public static PvpMaxDistanceRatio operator +(PvpMaxDistanceRatio a, PvpMaxDistanceRatio b)
        {
            return new PvpMaxDistanceRatio(a.Value + b.Value);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
