namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record NormalAttackCount(int Value)
    {
        public static NormalAttackCount Empty { get; } = new (0);

        public static TickCount operator *(NormalAttackCount a, TickCount b)
        {
            return new TickCount(a.Value * b.Value);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return IsEmpty() ? "-" : Value.ToString();
        }
    }
}
