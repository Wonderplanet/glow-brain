using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Domain.UseCase
{
    public class CheckOpenExchangeContentUseCase
    {
        [Inject] IMstExchangeShopDataRepository MstExchangeShopDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public bool CheckOpenExchangeContent(MasterDataId mstExchangeId)
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

            return mstModels.Exists(m => m.Id == mstExchangeId);
        }
    }
}
