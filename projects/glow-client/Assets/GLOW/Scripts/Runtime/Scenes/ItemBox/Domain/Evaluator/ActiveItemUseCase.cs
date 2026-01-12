using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.ItemBox.Domain.Evaluator
{
    public class ActiveItemUseCase
    {
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public bool IsActiveItem(MasterDataId mstItemId)
        {
            var item = MstItemDataRepository.GetItem(mstItemId);

            if(item.EndAt.IsUnlimitedEndAt) return true;

            return CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                item.StartAt,
                item.EndAt);
        }
    }
}
