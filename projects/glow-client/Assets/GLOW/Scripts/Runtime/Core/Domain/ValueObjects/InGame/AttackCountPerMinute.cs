namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AttackCountPerMinute(int Value)
    {
        public static AttackCountPerMinute Empty { get; } = new (0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
