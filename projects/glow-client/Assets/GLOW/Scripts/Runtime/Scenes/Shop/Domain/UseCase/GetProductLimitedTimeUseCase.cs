using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.UseCase
{
    public class GetProductLimitedTimeUseCase
    {
        [Inject] IValidatedStoreProductRepository ValidatedStoreProductRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        const int ThresholdDays = 30;

        public IReadOnlyList<KeyValuePair<MasterDataId, RemainingTimeSpan>> GetProductLimitedTime()
        {
            return ValidatedStoreProductRepository.GetValidatedStoreProducts()
                .Where(storeProduct => storeProduct.MstStoreProduct.ProductType == ProductType.Diamond)
                .Select(storeProduct =>
                {
                    var nowTime = TimeProvider.Now;
                    return new KeyValuePair<MasterDataId, RemainingTimeSpan>(storeProduct.MstStoreProduct.OprProductId,
                        CalculateRemainingTimeSpanWithThreshold(nowTime, storeProduct.MstStoreProduct.EndDate));
                }).ToList();
        }

        RemainingTimeSpan CalculateRemainingTimeSpanWithThreshold(DateTimeOffset nowTime, DateTimeOffset endDateTime)
        {
            var remainingTimeSpan = CalculateTimeCalculator.GetRemainingTime(nowTime, endDateTime);
            if (remainingTimeSpan.Value.TotalDays > ThresholdDays)
            {
                return RemainingTimeSpan.Empty;
            }

            return remainingTimeSpan;
        }

    }
}
