using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.FragmentProvisionRatio.Domain
{
    public class CheckTransitToShopUseCase
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        
        public bool ShouldTransitShopView(MasterDataId mstItemId)
        {
            var shopProducts = MstShopProductDataRepository.GetShopProducts();
            var products = shopProducts
                .Where(product => product.ResourceType == ResourceType.Item)
                .Where(product => product.ResourceId == mstItemId);
            
            // 有効な期間の商品がある場合はショップ画面に遷移する
            return products.Any(product => CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                product.StartDate,
                product.EndDate));
        }
    }
}