using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceRequireItemModel(
        ItemModel Item,
        EnoughCostFlag IsEnoughCost
    );
}
