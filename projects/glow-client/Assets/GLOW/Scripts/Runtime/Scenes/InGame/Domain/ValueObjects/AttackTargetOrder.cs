using System;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    /// <summary>
    /// 攻撃の対象になる優先度
    /// 小さい値ほど優先度が高い
    /// </summary>
    public record AttackTargetOrder(int Value) : IComparable<AttackTargetOrder>
    {
        public static AttackTargetOrder Empty { get; } = new(0);

        public static AttackTargetOrder Unit { get; } = new(1);
        public static AttackTargetOrder Outpost { get; } = new(2);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public int CompareTo(AttackTargetOrder other)
        {
            return Value.CompareTo(other.Value);
        }
    }
}
