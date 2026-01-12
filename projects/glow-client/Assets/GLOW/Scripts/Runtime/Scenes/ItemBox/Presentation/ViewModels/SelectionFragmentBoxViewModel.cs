using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ItemDetail.Presentation.Views;

namespace GLOW.Scenes.ItemBox.Presentation.ViewModels
{
    public record SelectionFragmentBoxViewModel(
        MasterDataId MstItemId,
        ItemDetailViewModel ItemDetail,
        ItemAmount LimitUseAmount,
        IReadOnlyList<SelectableLineupFragmentViewModel> Lineup,
        ItemDetailAvailableLocationViewModel AvailableLocation)
    {
        public bool IsAvailableLocation()
        {
           return AvailableLocation.EarnLocationViewModel1.TransitionPossibleFlag
                || AvailableLocation.EarnLocationViewModel2.TransitionPossibleFlag;
        }
    };
}
