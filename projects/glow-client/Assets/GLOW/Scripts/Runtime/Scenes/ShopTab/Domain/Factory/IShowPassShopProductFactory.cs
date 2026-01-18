using System.Collections.Generic;
using GLOW.Scenes.PassShop.Domain.Model;

namespace GLOW.Scenes.ShopTab.Domain.Factory
{
    public interface IShowPassShopProductFactory
    {
        IReadOnlyList<PassShopProductModel> GetPassShopProductList();
    }
}
