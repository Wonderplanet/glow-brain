using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EmblemAssetKey(ObscuredString Value)
    {
        public static EmblemAssetKey Empty { get; } = new(String.Empty);

        public PlayerResourceAssetKey ToPlayerResourceAssetKey()
        {
            return new PlayerResourceAssetKey(Value);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
