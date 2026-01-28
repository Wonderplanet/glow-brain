using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.BoxGacha.Domain.Provider
{
    public class MstBoxGachaProvider : IMstBoxGachaProvider
    {
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IMstBoxGachaDataRepository MstBoxGachaDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        
        MstBoxGachaModel IMstBoxGachaProvider.GetMstBoxGachaModelByEventId(MasterDataId mstEventId)
        {
            var mstEventModel = MstEventDataRepository.GetEventFirstOrDefault(mstEventId);
            if (mstEventModel.IsEmpty() ||
                !CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now,
                    mstEventModel.StartAt,
                    mstEventModel.EndAt))
            {
                return MstBoxGachaModel.Empty;
            }
            
            var mstBoxGachaModel = MstBoxGachaDataRepository.GetMstBoxGachaModelByMstEventIdFirstOrDefault(mstEventModel.Id);
            return mstBoxGachaModel;
        }
    }
}