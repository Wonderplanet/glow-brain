using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Notice;
using GLOW.Core.Extensions;
using GLOW.Scenes.ExchangeShop.Domain.Provider;
using GLOW.Scenes.Notice.Domain.Evaluator;
using GLOW.Scenes.Notice.Domain.Model;
using GLOW.Scenes.QuestContentTop.Domain.Factory;
using GLOW.Scenes.Shop.Domain.Calculator;
using WonderPlanet.CultureSupporter.Time;
using Zenject;

namespace GLOW.Scenes.Notice.Domain.Factory
{
    public class DisplayNoticeListFactory : IDisplayNoticeListFactory
    {
        const int MaxDisplayNoticeCount = 3;

        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IActiveExchangeShopLineupIdProvider ActiveExchangeShopLineupIdProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IShopProductModelCalculator ShopProductModelCalculator { get; }
        [Inject] IDisplayedInGameNoticeRepository DisplayedInGameNoticeRepository { get; }
        [Inject] IInGameNoticeModelFactory InGameNoticeModelFactory { get; }
        [Inject] IPvpQuestContentOpeningStatusModelFactory PvpQuestContentOpeningStatusModelFactory { get; }
        [Inject] INoticePassPurchasedEvaluator NoticePassPurchasedEvaluator { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        IReadOnlyList<NoticeModel> IDisplayNoticeListFactory.CreateDisplayNoticeList()
        {
            var fetchOtherModel = GameRepository.GetGameFetchOther();
            var oprInGameNoticeModel = fetchOtherModel.OprInGameNoticeModels;

            // IDが保存されていないものから上位3つ
            var inGameNoticeUseCaseModels = oprInGameNoticeModel
                .Where(IsDisplayNotice)
                .OrderByDescending(model => model.Priority)
                .Take(MaxDisplayNoticeCount)
                .Select(InGameNoticeModelFactory.Create).ToList();

            return inGameNoticeUseCaseModels;
        }

        bool IsDisplayNotice(OprNoticeModel model)
        {
            // 商品関連だった場合、その商品がもう買えない場合は表示しない
            if (model.DestinationPath.IsShopPath() &&
                !IsDisplayShopNotice(
                    model.DestinationPath,
                    model.DestinationPathDetail.ToMasterDataId()))
            {
                return false;
            }

            if (model.DestinationPath.IsPvpPath() &&
                !IsPvpOpen())
            {
                return false;
            }

            // 交換所でID指定の場合、開催中か確認
            if (model.DestinationPath.IsExchangePath() && !IsExchangeShopOpen(model))
            {
                return false;
            }

            switch (model.DisplayFrequencyType)
            {
                case IgnDisplayFrequencyType.Always:
                    return true;
                case IgnDisplayFrequencyType.Daily:
                    return !DisplayedInGameNoticeRepository.DisplayedDailyNoticeIdHashSet.Contains(model.Id);
                case IgnDisplayFrequencyType.Weekly:
                    return !DisplayedInGameNoticeRepository.DisplayedWeeklyNoticeIdHashSet.Contains(model.Id);
                case IgnDisplayFrequencyType.Monthly:
                    return !DisplayedInGameNoticeRepository.DisplayedMonthlyNoticeIdHashSet.Contains(model.Id);
                case IgnDisplayFrequencyType.Once:
                    return !DisplayedInGameNoticeRepository.DisplayedOnceNoticeIdHashSet.Contains(model.Id);
                default:
                    return true;
            }
        }

        bool IsPvpOpen()
        {
            var pvpOpeningStatusModel = PvpQuestContentOpeningStatusModelFactory.Create();
            return pvpOpeningStatusModel.IsOpening();
        }

        bool IsDisplayShopNotice(NoticeDestinationPath destinationPath, MasterDataId productId)
        {
            if(destinationPath == NoticeDestinationPath.ShopFree)
            {
                return IsTargetShopProductPurchasable(productId);
            }
            else
            {
                return IsTargetStoreProductPurchasable(productId);
            }
        }

        bool IsTargetShopProductPurchasable(MasterDataId productId)
        {
            var targetShopProduct = MstShopProductDataRepository.GetShopProducts()
                .FirstOrDefault(product => product.Id == productId, MstShopItemModel.Empty);

            if (targetShopProduct.IsEmpty()) return false;

            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userShopItemModel = gameFetchOther.UserShopItemModels
                .FirstOrDefault(product => product.MstShopItemId == productId, UserShopItemModel.Empty);

            var currentPurchasableCount =
                ShopProductModelCalculator.CalculatePurchasableCountCurrent(targetShopProduct, userShopItemModel);

            return !currentPurchasableCount.IsZero();
        }

        bool IsTargetStoreProductPurchasable(MasterDataId productId)
        {
            var targetStoreProduct = MstShopProductDataRepository.GetStoreProducts()
                .FirstOrDefault(product => product.OprProductId == productId, MstStoreProductModel.Empty);

            if (targetStoreProduct.IsEmpty()) return false;

            if (targetStoreProduct.ProductType == ProductType.Pass)
            {
                var targetProduct = MstShopProductDataRepository.GetShopPasses()
                    .FirstOrDefault(pass => pass.OprProductId == productId, MstShopPassModel.Empty);

                if (targetProduct.IsEmpty()) return false;

                if(!CalculateTimeCalculator.IsValidTime(TimeProvider.Now,
                       targetProduct.PassStartAt.Value,
                       targetProduct.PassEndAt.Value)) return false;

                return !NoticePassPurchasedEvaluator.IsTargetPassPurchased(targetProduct.MstShopPassId);
            }

            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userTargetStoreProduct = gameFetchOther.UserStoreProductModels
                .FirstOrDefault(product => product.ProductSubId == productId, UserStoreProductModel.Empty);

            var currentPurchasableCount =
                ShopProductModelCalculator.CalculatePurchasableCountCurrent(targetStoreProduct, userTargetStoreProduct);

            return !currentPurchasableCount.IsZero();
        }

        bool IsExchangeShopOpen(OprNoticeModel model)
        {
            if (model.DestinationPathDetail.IsEmpty()) return true;

            MasterDataId mstExchangeShopId = model.DestinationPathDetail.ToMasterDataId();
            return !ActiveExchangeShopLineupIdProvider.GetExchangeLineupId(mstExchangeShopId).IsEmpty();
        }
    }
}
