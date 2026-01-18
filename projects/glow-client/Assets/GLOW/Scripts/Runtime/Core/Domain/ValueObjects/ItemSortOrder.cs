using System;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ItemSortOrder(int Value) : IComparable<ItemSortOrder>
    {
        public int CompareTo(ItemSortOrder other)
        {
            return Value - other.Value;
        }
    }
}
