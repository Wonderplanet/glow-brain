using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.BoxGacha.Presentation.ViewModel
{
    public record BoxGachaInfoViewModel(
        BoxGachaPrizeStock TotalStockCount,
        BoxResetCount BoxResetCount,
        BoxDrawCount CurrentBoxTotalDrawnCount,
        BoxLevel CurrentBoxLevel,
        PlayerResourceIconViewModel CostResourceIconViewModel,
        CostAmount CostAmount,
        RemainingTimeSpan RemainingTimeSpan,
        IReadOnlyList<BoxGachaRewardListCellViewModel> BoxGachaRewardListCellViewModels)
    {
        public static BoxGachaInfoViewModel Empty { get; } = new(
            BoxGachaPrizeStock.Empty,
            BoxResetCount.Empty,
            BoxDrawCount.Empty,
            BoxLevel.Empty,
            PlayerResourceIconViewModel.Empty,
            CostAmount.Empty,
            RemainingTimeSpan.Empty,
            new List<BoxGachaRewardListCellViewModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}