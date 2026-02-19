using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.UseCase
{
    public class GetShopNextUpdateTimeUseCase
    {
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }

        public IReadOnlyDictionary<DisplayShopProductType, RemainingTimeSpan> GetShopNextUpdateTimes()
        {
            return new Dictionary<DisplayShopProductType, RemainingTimeSpan>
            {
                {DisplayShopProductType.Diamond, CalculateRemainingTimeSpan(DisplayShopProductType.Diamond)},
                {DisplayShopProductType.Daily, CalculateRemainingTimeSpan(DisplayShopProductType.Daily)},
                {DisplayShopProductType.Weekly, CalculateRemainingTimeSpan(DisplayShopProductType.Weekly)},
                {DisplayShopProductType.Coin, CalculateRemainingTimeSpan(DisplayShopProductType.Coin)}
            };
        }

        RemainingTimeSpan CalculateRemainingTimeSpan(DisplayShopProductType shopProductType)
        {
            return new RemainingTimeSpan(GetNextUpdateRemainingTime(shopProductType));
        }

        TimeSpan GetNextUpdateRemainingTime(DisplayShopProductType shopProductType)
        {
            return shopProductType switch
            {
                DisplayShopProductType.Daily => DailyResetTimeCalculator.GetRemainingTimeToDailyReset(),
                DisplayShopProductType.Weekly => DailyResetTimeCalculator.GetRemainingTimeToWeeklyReset(),
                DisplayShopProductType.Coin => DailyResetTimeCalculator.GetRemainingTimeToDailyReset(),
                _ =>  TimeSpan.Zero
            };
        }
    }
}
