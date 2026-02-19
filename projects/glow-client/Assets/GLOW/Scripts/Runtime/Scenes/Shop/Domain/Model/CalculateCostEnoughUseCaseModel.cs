using GLOW.Core.Domain.Constants.Shop;

namespace GLOW.Scenes.Shop.Domain.Model
{
    public record CalculateCostEnoughUseCaseModel(
        DisplayCostType DisplayCostType,
        long CurrentResourceAmount,
        long AfterResourceAmount,
        bool IsEnough
        );
}
