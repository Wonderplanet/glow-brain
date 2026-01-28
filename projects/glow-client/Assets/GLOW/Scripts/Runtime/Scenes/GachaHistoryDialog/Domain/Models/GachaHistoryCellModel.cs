using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaHistoryDialog.Domain.Models
{
    public record GachaHistoryCellModel(
        DateTimeOffset GachaDrawDate,
        GachaName GachaName,
        AdDrawFlag AdDrawFlag,
        PlayerResourceIconAssetPath CostItemPlayerResourceIconAssetPath,
        CostAmount CostAmount);
}