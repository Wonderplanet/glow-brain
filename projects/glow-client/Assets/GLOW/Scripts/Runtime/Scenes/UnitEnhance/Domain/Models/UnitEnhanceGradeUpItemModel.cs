using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceGradeUpItemModel(ItemName ItemName, ItemAmount CurrentAmount, ItemAmount RequireAmount);
}
