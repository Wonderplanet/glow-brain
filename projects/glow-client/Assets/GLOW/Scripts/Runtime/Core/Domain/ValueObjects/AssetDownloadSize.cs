using System;
using GLOW.Scenes.Title.Domains.Formatter;

namespace GLOW.Core.Domain.ValueObjects
{
    public record AssetDownloadSize(ulong Value) : IComparable
    {
        public bool IsZero()
        {
            return Value == 0;
        }

        public string ToStringSeparated()
        {
            return DataSizeFormatter.ConvertToStringMBOrMore(Value);
        }

        public static bool operator < (AssetDownloadSize a, FreeSpaceSize b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <= (AssetDownloadSize a, FreeSpaceSize b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator > (AssetDownloadSize a, FreeSpaceSize b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >= (AssetDownloadSize a, FreeSpaceSize b)
        {
            return a.Value >= b.Value;
        }

        public int CompareTo(object obj)
        {
            if (obj is AssetDownloadSize other)
            {
                return Value.CompareTo(other.Value);
            }

            return -1;
        }
    }
}
