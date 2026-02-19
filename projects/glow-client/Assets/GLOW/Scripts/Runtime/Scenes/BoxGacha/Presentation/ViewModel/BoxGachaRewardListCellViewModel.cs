using System.Collections.Generic;

namespace GLOW.Scenes.BoxGacha.Presentation.ViewModel
{
    public record BoxGachaRewardListCellViewModel(IReadOnlyList<BoxGachaPrizeCellViewModel> PrizeCellViewModelList)
    {
        public static BoxGachaRewardListCellViewModel Empty { get; } = new(new List<BoxGachaPrizeCellViewModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}