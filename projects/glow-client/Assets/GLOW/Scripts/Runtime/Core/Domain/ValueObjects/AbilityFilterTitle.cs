using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record AbilityFilterTitle(ObscuredString Value)
    {
        public static AbilityFilterTitle Empty { get; } = new(String.Empty);
    }
}
