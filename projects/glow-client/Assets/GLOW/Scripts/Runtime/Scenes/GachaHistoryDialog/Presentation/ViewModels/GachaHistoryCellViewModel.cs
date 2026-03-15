using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaHistoryDialog.Presentation.ViewModels
{
    public record GachaHistoryCellViewModel(
        DateTimeOffset GachaDrawDate,
        GachaName GachaName,
        CostType　CostType,
        PlayerResourceIconAssetPath PlayerResourceIconAssetPath,
        CostAmount CostAmount)
    {
        public static GachaHistoryCellViewModel Empty { get; } = new GachaHistoryCellViewModel(
            DateTimeOffset.MinValue,
            GachaName.Empty,
            CostType.Coin,
            PlayerResourceIconAssetPath.Empty,
            CostAmount.Empty);

        public AdDrawFlag IsAdDraw()
        {
            return CostType == CostType.Ad ? AdDrawFlag.True : AdDrawFlag.False;
        }

        public bool IsFreeDraw()
        {
            return CostType == CostType.Free;
        }

        public bool IsDisplayCostIcon()
        {
            return !PlayerResourceIconAssetPath.IsEmpty();
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}