using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record BnIdCode(ObscuredString Value)
    {
        public static BnIdCode Empty { get; } = new BnIdCode(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
