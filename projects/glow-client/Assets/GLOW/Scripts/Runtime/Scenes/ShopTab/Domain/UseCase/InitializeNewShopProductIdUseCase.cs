using System;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.ShopTab.Domain.UseCase
{
    public class InitializeNewShopProductIdUseCase
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IShopProductCacheRepository ProductCacheRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }

        public void InitializeNewShopProductId()
        {
            var nowTime = TimeProvider.Now;
            var lastCheckedNewShopProductDateTimeOffset = ProductCacheRepository.LastCheckedNewShopProductDateTimeOffset;
            if (lastCheckedNewShopProductDateTimeOffset == DateTimeOffset.MinValue)
            {
                ProductCacheRepository.SaveLastCheckedNewShopProductDateTimeOffset(nowTime);
                return;
            }
            
            InitializeDailyNewShopProductIds(lastCheckedNewShopProductDateTimeOffset);
            InitializeWeeklyNewShopProductIds(lastCheckedNewShopProductDateTimeOffset);
            
            ProductCacheRepository.SaveLastCheckedNewShopProductDateTimeOffset(nowTime);
        }

        void InitializeDailyNewShopProductIds(DateTimeOffset nowTime)
        {
            if (!DailyResetTimeCalculator.IsPastDailyRefreshTime(nowTime)) return;
            
            // デイリー商品のIDを取得する
            var dailyShopProductIds = MstShopProductDataRepository
                .GetShopProducts()
                .Where(product => product.ShopType == ShopType.Daily)
                .Select(product => product.Id)
                .ToHashSet();
            
            // デイリー商品に含まれているもの以外のIDを取得する(デイリー商品を消す)
            var cachedDisplayedShopProductIds = ProductCacheRepository.DisplayedShopProductIdHashSet;
            var updatedDailyShopProductIds = cachedDisplayedShopProductIds
                .Where(id => !dailyShopProductIds.Contains(id))
                .ToHashSet();
            
            ProductCacheRepository.SetDisplayedShopProductIdHashSet(updatedDailyShopProductIds);
        }

        void InitializeWeeklyNewShopProductIds(DateTimeOffset nowTime)
        {
            if (!DailyResetTimeCalculator.IsPastWeeklyRefreshTime(nowTime)) return;

            // ウィークリー商品のIDを取得する
            var weeklyShopProductIds = MstShopProductDataRepository
                .GetShopProducts()
                .Where(product => product.ShopType == ShopType.Weekly)
                .Select(product => product.Id)
                .ToHashSet();

            // ウィークリー商品に含まれているもの以外のIDを取得する(ウィークリー商品を消す)
            var cachedDisplayedShopProductIds = ProductCacheRepository.DisplayedShopProductIdHashSet;
            var updatedWeeklyShopProductIdHashSet = cachedDisplayedShopProductIds
                .Where(id => !weeklyShopProductIds.Contains(id))
                .ToHashSet();

            ProductCacheRepository.SetDisplayedShopProductIdHashSet(updatedWeeklyShopProductIdHashSet);
        }
    }
}
