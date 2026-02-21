using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Scenes.PackShop.Domain.Models
{
    public record PackShopValidateProductModel(
        MstPackModel MstPack,
        ValidatedStoreProductModel ValidatedStoreProduct
    )
    {
        public static PackShopValidateProductModel Empty { get; } = new PackShopValidateProductModel(
            MstPackModel.Empty,
            ValidatedStoreProductModel.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
