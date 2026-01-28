namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record SpecialAttackCount(int Value)
    {
        public static SpecialAttackCount Empty { get; } = new (0);
        public static SpecialAttackCount Zero { get; } = new (0);
        public static SpecialAttackCount One { get; } = new (1);

        public static TickCount operator *(SpecialAttackCount a, TickCount b)
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
