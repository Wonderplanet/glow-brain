using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ExchangeShop.Domain.UseCaseModel;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;
using GLOW.Scenes.ExchangeShop.Domain.UseCaseModel;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Domain.UseCase
{
    public class GetActiveExchangeContentUseCase
    {
        [Inject] IMstExchangeShopDataRepository MstExchangeShopDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public IReadOnlyList<ActiveExchangeContentUseCaseModel> GetActiveTradeContents()
        {
            // 掲載期間内の交換所を取り出す
            var mstModels = MstExchangeShopDataRepository.GetTradeContents()
                .Where(m =>
                    CalculateTimeCalculator.IsValidTime(
                        TimeProvider.Now,
                        m.StartAt.Value,
                        m.EndAt.Value)
                    )
                .ToList();

            var useCaseModels = mstModels
                .Select(Translate)
                .OrderBy(m => m.SortOrder)
                .ToList();

            return useCaseModels;
        }

        ActiveExchangeContentUseCaseModel Translate(MstExchangeModel mstExchangeModel)
        {
            var remainingTime = CalculateTimeCalculator.GetRemainingTime(TimeProvider.Now, mstExchangeModel.EndAt.Value);

            return new ActiveExchangeContentUseCaseModel(
                mstExchangeModel.Id,
                mstExchangeModel.MstGroupId,
                mstExchangeModel.BannerAssetKey,
                mstExchangeModel.TradeType,
                remainingTime,
                mstExchangeModel.EndAt,
                mstExchangeModel.SortOrder);
        }
    }
}
