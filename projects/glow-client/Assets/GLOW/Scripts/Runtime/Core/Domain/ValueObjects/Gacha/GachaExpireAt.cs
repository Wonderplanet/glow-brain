using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaExpireAt(ObscuredDateTimeOffset Value)
    {
        public static GachaExpireAt Empty { get; } = new(DateTimeOffset.MinValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}