using System;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record CanExecuteRushFlag(bool Value) : IComparable
    {
        public static CanExecuteRushFlag False { get; } = new(false);
        public static CanExecuteRushFlag True { get; } = new(true);
        public static implicit operator bool(CanExecuteRushFlag value) => value.Value;
        public static bool operator true(CanExecuteRushFlag value) => value.Value;
        public static bool operator false(CanExecuteRushFlag value) => !value.Value;

        public int CompareTo(object obj)
        {
            if (obj is CanExecuteRushFlag other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }
    }
}
