using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record SeriesAssetKey(ObscuredString Value) : IComparable
    {
        public static SeriesAssetKey Empty { get; } = new(string.Empty);
        public int CompareTo(object obj)
        {
            if (obj is SeriesAssetKey other)
            {
                return string.Compare(Value, other.Value, StringComparison.Ordinal);
            }

            return -1;
        }
    }
}
