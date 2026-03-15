using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaHistoryDialog.Domain.Models
{
    public record GachaHistoryCellModel(
        DateTimeOffset GachaDrawDate,
        GachaName GachaName,
        CostType CostType,
        PlayerResourceIconAssetPath CostItemPlayerResourceIconAssetPath,
        CostAmount CostAmount);
}