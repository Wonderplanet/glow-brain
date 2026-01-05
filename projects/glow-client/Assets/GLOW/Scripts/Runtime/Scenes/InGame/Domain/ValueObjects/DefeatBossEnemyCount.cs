namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record DefeatBossEnemyCount(int Value)
    {
        public static DefeatBossEnemyCount Empty { get; } = new(0);

        public static DefeatBossEnemyCount operator +(DefeatBossEnemyCount a, DefeatBossEnemyCount b)
        {
            return new DefeatBossEnemyCount(a.Value + b.Value);
        }
        
        public static bool operator <(DefeatBossEnemyCount a, int b)
        {
            return a.Value < b;
        }

        public static bool operator <=(DefeatBossEnemyCount a, int b)
        {
            return a.Value <= b;
        }

        public static bool operator >(DefeatBossEnemyCount a, int b)
        {
            return a.Value > b;
        }

        public static bool operator >=(DefeatBossEnemyCount a, int b)
        {
            return a.Value >= b;
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
