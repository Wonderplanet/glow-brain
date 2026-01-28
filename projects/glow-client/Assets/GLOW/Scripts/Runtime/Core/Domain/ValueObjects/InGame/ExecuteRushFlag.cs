using System;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record ExecuteRushFlag(bool Value) : IComparable
    {
        public static ExecuteRushFlag False { get; } = new(false);
        public static ExecuteRushFlag True { get; } = new(true);
        public static implicit operator bool(ExecuteRushFlag value) => value.Value;
        public static bool operator true(ExecuteRushFlag value) => value.Value;
        public static bool operator false(ExecuteRushFlag value) => !value.Value;

        public static ExecuteRushFlag operator !(ExecuteRushFlag value) => new(!value.Value);

        public int CompareTo(object obj)
        {
            if (obj is ExecuteRushFlag other)
            {
                return Value.CompareTo(other.Value);
            }
            return 1;
        }
    }
}
