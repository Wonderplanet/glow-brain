using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.ItemDetail.Domain.Models
{
    public record ItemDetailWithTransitModel(
        PlayerResourceModel PlayerResourceModel,
        ItemDetailAmountModel ItemDetailAmountModel,
        ItemDetailAvailableLocationModel ItemDetailAvailableLocationModel)
    {
        public static ItemDetailWithTransitModel Empty { get; } = new ItemDetailWithTransitModel(
            PlayerResourceModel.Empty,
            ItemDetailAmountModel.Empty,
            ItemDetailAvailableLocationModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}