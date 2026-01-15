using GLOW.Core.Presentation.Components;
using GLOW.Scenes.FragmentProvisionRatio.Presentation.DestinationBanner;
using GLOW.Scenes.GachaLineupDialog.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.GachaLineupDialog.Presentation.Views.Components
{
    public class GachaLineupPageComponent : UIObject
    {
        [SerializeField] GachaLineupListComponent _pickupComponent;
        [SerializeField] GachaLineupListComponent _lineupComponent;

        public void Setup(GachaLineupPageViewModel viewModel)
        {
            if (viewModel.IsEmpty()) return;

            _pickupComponent.Setup(viewModel.GachaPickupListViewModel);
            _lineupComponent.Setup(viewModel.GachaLineupListViewModel);
        }
    }
}
