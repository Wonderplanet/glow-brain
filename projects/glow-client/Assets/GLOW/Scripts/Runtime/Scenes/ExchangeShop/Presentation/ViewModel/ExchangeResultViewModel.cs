using System.Collections.Generic;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.ViewModels;
using GLOW.Scenes.UnitReceive.Presentation.ViewModel;

namespace GLOW.Scenes.ExchangeShop.Presentation.ViewModel
{
    public record ExchangeResultViewModel(
        IReadOnlyList<CommonReceiveResourceViewModel> RewardModels,
        ArtworkFragmentAcquisitionViewModel ArtworkFragmentAcquisitionViewModel,
        UnitReceiveViewModel UnitReceiveViewModel)
    {
        public bool IsArtworkReward()
        {
            return ArtworkFragmentAcquisitionViewModel?.IsEmpty() == false;
        }

        public bool IsUnitReward()
        {
            return UnitReceiveViewModel?.IsEmpty() == false;
        }
    }
}
