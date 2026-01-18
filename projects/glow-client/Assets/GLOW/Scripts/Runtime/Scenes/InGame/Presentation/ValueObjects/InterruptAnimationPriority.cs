using System;

namespace GLOW.Scenes.InGame.Presentation.ValueObjects
{
    public record InterruptAnimationPriority(int Value) : IComparable<InterruptAnimationPriority>
    {
        public static InterruptAnimationPriority Empty { get; } = new(0);

        public static bool operator >(InterruptAnimationPriority left, InterruptAnimationPriority right)
        {
            return left.Value > right.Value;
        }

        public static bool operator <(InterruptAnimationPriority left, InterruptAnimationPriority right)
        {
            return left.Value < right.Value;
        }

        public static bool operator >=(InterruptAnimationPriority left, InterruptAnimationPriority right)
        {
            return left.Value >= right.Value;
        }

        public static bool operator <=(InterruptAnimationPriority left, InterruptAnimationPriority right)
        {
            return left.Value <= right.Value;
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public int CompareTo(InterruptAnimationPriority other)
        {
            return Value.CompareTo(other.Value);
        }
    }
}