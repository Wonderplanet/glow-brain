using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.GachaRatio.Presentation.ViewModels
{
    public record GachaRatioRarityRatioItemViewModel(Rarity Rarity, float Probability)
    {
        public OutputRatio OutputRatio { get; } = new OutputRatio((Decimal)Probability);
    };
}