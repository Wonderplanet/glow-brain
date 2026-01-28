using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Scenes.Shop.Domain.Model;

namespace GLOW.Scenes.Shop.Domain.Factories
{
    public interface IConfirmationShopProductModelFactory
    {
        ConfirmationShopProductModel Create(MstShopItemModel mstShopItemModel);
        ConfirmationShopProductModel Create(ValidatedStoreProductModel validateStoreProductModel);
    }
}
