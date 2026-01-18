using System;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PickUpFlag(bool Value) : IComparable
    {
        public static PickUpFlag False { get; } = new(false);
        public static PickUpFlag True { get; } = new(true);
        public static implicit operator bool(PickUpFlag value) => value.Value;
        public static bool operator true(PickUpFlag value) => value.Value;
        public static bool operator false(PickUpFlag value) => !value.Value;
        public int CompareTo(object obj)
        {
            if (obj is PickUpFlag other)
            {
                return Value.CompareTo(other.Value);
            }
            return 1;
        }
    }
}
