
namespace GLOW.Scenes.Shop.Domain.Model
{
    public record AdvertisementProductBuyConfirmationModel(ConfirmationShopProductModel ProductModel)
    {
         public static AdvertisementProductBuyConfirmationModel Empty { get; } = new(ConfirmationShopProductModel.Empty);

         public bool IsEmpty()
         {
             return ReferenceEquals(this, Empty);
         }
    }
}
