using System.Collections.Generic;
using GLOW.Scenes.PassShop.Domain.Model;
using GLOW.Scenes.ShopTab.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.PassShop.Domain.UseCase
{
    public class ShowPassShopProductListUseCase
    {
        [Inject] IShowPassShopProductFactory ShowPassShopProductFactory { get; }

        public IReadOnlyList<PassShopProductModel> GetPassShopProductList()
        {
            return ShowPassShopProductFactory.GetPassShopProductList();
        }
    }
}
