using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.BoxGacha.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.BoxGacha.Domain.Evaluator
{
    public class BoxGachaDrawableEvaluator : IBoxGachaDrawableEvaluator
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstBoxGachaDataRepository MstBoxGachaDataRepository { get; }
        
        public BoxGachaDrawableFlag Evaluate(MasterDataId mstEventId)
        {
            var mstBoxGachaModel = MstBoxGachaDataRepository.GetMstBoxGachaModelByMstEventIdFirstOrDefault(mstEventId);
            if (mstBoxGachaModel.IsEmpty())
            {
                return BoxGachaDrawableFlag.False;
            }

            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userCostItem = gameFetchOther.UserItemModels
                .FirstOrDefault(item => item.MstItemId == mstBoxGachaModel.CostId, UserItemModel.Empty);
            var oneDrawCostAmount = mstBoxGachaModel.CostAmount;
            
            return userCostItem.Amount >= oneDrawCostAmount
                ? BoxGachaDrawableFlag.True
                : BoxGachaDrawableFlag.False;
        }
    }
}