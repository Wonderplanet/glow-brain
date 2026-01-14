using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AttackComboCycle(ObscuredInt Value)
    {
        public static AttackComboCycle Empty { get; } = new(1);

        public ObscuredInt Value { get; } = Value >= 1 ? Value : 1;

        public NormalAttackCount NormalAttackCountBeforeSpecial => Value == 1
            ? NormalAttackCount.Empty
            : new NormalAttackCount(Value - 1);

        public NormalAttackCount NormalAttackCount => Value == 1
            ? new NormalAttackCount(Value)
            : new NormalAttackCount(Value - 1);

        public SpecialAttackCount SpecialAttackCount => Value == 1
            ? SpecialAttackCount.Zero
            : SpecialAttackCount.One;

        public static bool operator <(AttackComboCycle a, int b)
        {
            return a.Value < b;
        }

        public static bool operator <=(AttackComboCycle a, int b)
        {
            return a.Value <= b;
        }

        public static bool operator >(AttackComboCycle a, int b)
        {
            return a.Value > b;
        }

        public static bool operator >=(AttackComboCycle a, int b)
        {
            return a.Value >= b;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
