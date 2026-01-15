using System;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.UseCase
{
    public class GetNextUpdateTimeUseCase
    {
        [Inject] ITimeProvider TimeProvider { get; }

        public RemainingTimeSpan GetNextUpdateTime(DisplayShopProductType shopProductType)
        {
            var currentUpdateDate = TimeProvider.Now;
            var nextUpdateDate = GetNextUpdateDate(shopProductType, currentUpdateDate);
            return new RemainingTimeSpan(nextUpdateDate - currentUpdateDate);
        }

        DateTimeOffset GetNextUpdateDate(DisplayShopProductType shopProductType, DateTimeOffset nowTime)
        {
            return shopProductType switch
            {
                DisplayShopProductType.Daily => CalculateTimeCalculator.GetNextDay(nowTime),
                DisplayShopProductType.Weekly => CalculateTimeCalculator.GetNextWeek(nowTime),
                _ =>  nowTime
            };
        }
    }
}
