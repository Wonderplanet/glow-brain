using System;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record DeckAutoPlayerSummonPriority(int Value) : IComparable
    {
        public static DeckAutoPlayerSummonPriority Empty { get; } = new(0);
        public static DeckAutoPlayerSummonPriority One { get; } = new(1);
        public static DeckAutoPlayerSummonPriority Two { get; } = new(2);

        
        public static DeckAutoPlayerSummonPriority operator *(DeckAutoPlayerSummonPriority a, DeckAutoPlayerSummonPriority b)
        {
            return new DeckAutoPlayerSummonPriority(a.Value * b.Value);
        }
        
        public static DeckAutoPlayerSummonPriority operator +(DeckAutoPlayerSummonPriority a, DeckAutoPlayerSummonPriority b)
        {
            return new DeckAutoPlayerSummonPriority(a.Value + b.Value);
        }
        
        public static DeckAutoPlayerSummonPriority operator +(DeckAutoPlayerSummonPriority a, int b)
        {
            return new DeckAutoPlayerSummonPriority(a.Value + b);
        }

        public int CompareTo(object obj)
        {
            if (obj is DeckAutoPlayerSummonPriority other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }
    }
}