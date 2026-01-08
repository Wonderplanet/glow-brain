using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.EventQuestSelect.Domain.UseCase
{
    public class EventExchangeShopCheckUseCase
    {
        [Inject] IMstExchangeShopDataRepository MstShopDataRepository { get; }

        public MasterDataId GetEventExchangeShop(MasterDataId mstEventId)
        {
            MasterDataId mstExchangeId = MasterDataId.Empty;

            var exchangeShop = MstShopDataRepository.GetTradeContents()
                .FirstOrDefault(m => m.MstEventId == mstEventId, MstExchangeModel.Empty);

            if(!exchangeShop.IsEmpty()) mstExchangeId = exchangeShop.Id;

            return mstExchangeId;
        }
    }
}
