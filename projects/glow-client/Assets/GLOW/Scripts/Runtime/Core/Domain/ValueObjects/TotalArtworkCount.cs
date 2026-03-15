using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record TotalArtworkCount(ObscuredInt Value) : IComparable
    {
        public static TotalArtworkCount Empty { get; } = new TotalArtworkCount(0);

        public override string ToString()
        {
            return Value.ToString("N0", null);
        }

        public int CompareTo(object obj)
        {
            if (obj is TotalArtworkCount other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }
    }
}
