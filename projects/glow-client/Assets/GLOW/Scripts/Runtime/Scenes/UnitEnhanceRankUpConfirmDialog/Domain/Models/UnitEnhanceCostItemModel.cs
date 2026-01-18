using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Domain.Models
{
    public record UnitEnhanceCostItemModel(ItemModel Item, ItemAmount PossessionAmount)
    {

    }
}
