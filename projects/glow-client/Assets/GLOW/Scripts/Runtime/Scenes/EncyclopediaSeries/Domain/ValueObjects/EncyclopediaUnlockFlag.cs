using System;

namespace GLOW.Scenes.EncyclopediaSeries.Domain.ValueObjects
{
    public record EncyclopediaUnlockFlag(bool Value) : IComparable<EncyclopediaUnlockFlag>
    {
        public int CompareTo(EncyclopediaUnlockFlag other)
        {
            return Value.CompareTo(other.Value);
        }

        public static implicit operator bool(EncyclopediaUnlockFlag flag) => flag.Value;
    }
}
