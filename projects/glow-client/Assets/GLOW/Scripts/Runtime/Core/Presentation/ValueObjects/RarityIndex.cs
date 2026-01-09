using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Presentation.ValueObjects
{
    public record RarityIndex(Rarity Rarity)
    {
        public static RarityIndex Empty { get; } = new(Rarity.R);

        public int Index => (int)Rarity;

        public static bool operator <(RarityIndex a, int b)
        {
            return a.Index < b;
        }

        public static bool operator <=(RarityIndex a, int b)
        {
            return a.Index <= b;
        }

        public static bool operator >(RarityIndex a, int b)
        {
            return a.Index > b;
        }

        public static bool operator >=(RarityIndex a, int b)
        {
            return a.Index >= b;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
