using System;
using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pass
{
    public record PassStartAt(ObscuredDateTimeOffset Value)
    {
        public static PassStartAt Empty { get; } = new(DateTimeOffset.MinValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public string ToFormattedString()
        {
            return Value.ToString("yyyy/MM/dd HH:mm:ss", CultureInfo.InvariantCulture);
        }
    }
}