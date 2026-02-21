using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using GLOW.Scenes.PackShop.Domain.Calculator;
using GLOW.Scenes.PackShop.Domain.Models;
using Zenject;

namespace GLOW.Scenes.ShopTab.Domain.UseCase
{
    public class GetPackProductNoticeUseCase
    {
        [Inject] IShopProductCacheRepository ShopProductCacheRepository { get; }

        [Inject] IPackShopProductEvaluator PackShopProductEvaluator { get; }

        [Inject] IGameRepository GameRepository { get; }
        public bool GetPackProductNotice()
        {
            var displayedPackProductIds = ShopProductCacheRepository.DisplayedOprPackProductIds;
            if (displayedPackProductIds.Count == 0)
                return true;
            
            var validPacks = PackShopProductEvaluator.GetValidateProductList();

            var allProducts = new List<PackShopValidateProductModel>();
            allProducts.AddRange(validPacks.StageClearPacks);
            allProducts.AddRange(validPacks.NormalPacks);
            allProducts.AddRange(validPacks.DailyPacks);

            return IsDisplayNotice(allProducts);
        }

        bool IsDisplayNotice(List<PackShopValidateProductModel> productModels)
        {
            var displayedPackProductIdHashSet = ShopProductCacheRepository.DisplayedOprPackProductIds.ToHashSet();
            
            // 全てのパックが端末保存されているか？ -> されていれば表示しないのでfalse
            var isAllContainPack = productModels.All(model => displayedPackProductIdHashSet.Contains(model.MstPack.ProductSubId));

            // 無料商品・広告商品のなかで受け取り可能なものがあるか？ -> 受け取り可能なものがあれば表示するのでtrue
            var isReceivableFreeOrAdProduct = IsReceivableFreeOrAdProduct(productModels);

            return !isAllContainPack || isReceivableFreeOrAdProduct;
        }

        bool IsReceivableFreeOrAdProduct(List<PackShopValidateProductModel> productModels)
        {
            var userTradePackModels = GameRepository.GetGameFetchOther().UserTradePackModels;
            var result = productModels
                .Where(p => p.MstPack.CostType is CostType.Free or CostType.Ad)
                .Any(p =>
                {
                    var userTradePackModel = userTradePackModels.FirstOrDefault(u => u.MstPackId == p.MstPack.Id);
                    if (userTradePackModel == null) return true;

                    // 残り購入可能回数をチェック
                    var remainingCount = p.MstPack.TradableCount - userTradePackModel.DailyTradeCount;
                    return remainingCount.IsPurchasable();
                });
            return result;
        }
    }
}
