using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaHistoryDialog.Presentation.ViewModels
{
    public record GachaHistoryCellViewModel(
        DateTimeOffset GachaDrawDate,
        GachaName GachaName,
        AdDrawFlag　IsAdDraw,
        PlayerResourceIconAssetPath PlayerResourceIconAssetPath,
        CostAmount CostAmount)
    {
        public static GachaHistoryCellViewModel Empty { get; } = new GachaHistoryCellViewModel(
            DateTimeOffset.MinValue,
            GachaName.Empty,
            AdDrawFlag.False,
            PlayerResourceIconAssetPath.Empty,
            CostAmount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}