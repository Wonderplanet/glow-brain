using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ItemDetail.Domain.Models
{
    public record ItemDetailAmountModel(
        PlayerCurrentAmount CurrentAmount,
        PlayerCurrentAmount PaidDiamondAmount)
    {
        public static ItemDetailAmountModel Empty = new ItemDetailAmountModel(
            PlayerCurrentAmount.Empty,
            PlayerCurrentAmount.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
