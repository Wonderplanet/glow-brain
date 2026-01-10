using System.Linq;
using GLOW.Scenes.ShopTab.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.ShopTab.Domain.UseCase
{
    public class GetPassProductNoticeUseCase
    {
        [Inject] IShowPassShopProductFactory ShowPassShopProductFactory { get; }

        public bool GetPassProductNotice()
        {
            var passProductList = ShowPassShopProductFactory.GetPassShopProductList();

            return passProductList.Any(p => p.PassEffectValidRemainingTime.IsEmpty());
        }
    }
}
