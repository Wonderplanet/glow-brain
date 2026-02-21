using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record AttackComboCount(int Value)
    {
        public static AttackComboCount Zero { get; } = new (0);
        public static AttackComboCount operator +(AttackComboCount a, int b)
        {
            return new AttackComboCount(a.Value + b);
        }

        public static bool operator <(AttackComboCount a, int b)
        {
            return a.Value < b;
        }

        public static bool operator <=(AttackComboCount a, int b)
        {
            return a.Value <= b;
        }

        public static bool operator >(AttackComboCount a, int b)
        {
            return a.Value > b;
        }

        public static bool operator >=(AttackComboCount a, int b)
        {
            return a.Value >= b;
        }

        public bool IsSpecialAttack(AttackComboCycle comboCycle)
        {
            return Value > 1 && Value == comboCycle.Value;
        }

        public bool IsMaxCombo(AttackComboCycle comboCycle)
        {
            return Value >= comboCycle.Value;
        }

        public AttackComboCount NextComboCount(AttackComboCycle comboCycle)
        {
            return IsMaxCombo(comboCycle) ? new AttackComboCount(1) : this + 1;
        }
    }
}
