using System;
using GLOW.Core.Domain.Constants;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.Models.Gacha
{
    public record GachaRarityProbabilityModel(Rarity Rarity, ObscuredFloat Probability)
    {
        public static GachaRarityProbabilityModel Empty { get; } = new(Rarity.R, 0f);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
