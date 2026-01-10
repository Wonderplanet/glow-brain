namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record DefeatEnemyCount(int Value)
    {
        public static DefeatEnemyCount Empty { get; } = new(0);
        public static DefeatEnemyCount Zero { get; } = new(0);
        public static DefeatEnemyCount One { get; } = new(1);
        
        public static DefeatEnemyCount Max(DefeatEnemyCount a, DefeatEnemyCount b)
        {
            return a.Value > b.Value ? a : b;
        }

        public static DefeatEnemyCount operator +(DefeatEnemyCount a, DefeatEnemyCount b)
        {
            return new DefeatEnemyCount(a.Value + b.Value);
        }
        
        public static DefeatEnemyCount operator +(DefeatEnemyCount a, int b)
        {
            return new DefeatEnemyCount(a.Value + b);
        }

        public static DefeatEnemyCount operator -(DefeatEnemyCount a, DefeatEnemyCount b)
        {
            return new DefeatEnemyCount(a.Value - b.Value);
        }

        public static bool operator <(DefeatEnemyCount a, DefeatEnemyCount b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(DefeatEnemyCount a, DefeatEnemyCount b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(DefeatEnemyCount a, DefeatEnemyCount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(DefeatEnemyCount a, DefeatEnemyCount b)
        {
            return a.Value >= b.Value;
        }

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
