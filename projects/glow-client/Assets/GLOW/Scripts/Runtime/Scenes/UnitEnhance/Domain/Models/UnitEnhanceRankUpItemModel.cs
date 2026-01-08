using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceRankUpItemModel(ItemName ItemName, ItemAmount CurrentAmount, ItemAmount RequireAmount);
}
