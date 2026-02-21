using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ItemDetail.Presentation.ViewModels
{
    public record ItemDetailAmountViewModel(
        PlayerCurrentAmount CurrentAmount,
        PlayerCurrentAmount PaidDiamondAmount);
}