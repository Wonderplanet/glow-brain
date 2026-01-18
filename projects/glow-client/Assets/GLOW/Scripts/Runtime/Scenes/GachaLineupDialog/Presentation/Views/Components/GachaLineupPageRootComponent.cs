using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaLineupDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaRatio.Domain.Constants;
using UnityEngine;

namespace GLOW.Scenes.GachaLineupDialog.Presentation.Views.Components
{
    public class GachaLineupPageRootComponent : UIObject
    {
        [SerializeField] GachaLineupPageComponent _normalRatioPageComponent;
        [SerializeField] GachaLineupPageComponent _ssrRatioPageComponent;
        [SerializeField] GachaLineupPageComponent _urRatioPageComponent;
        [SerializeField] GachaLineupPageComponent _pickupRatioPageComponent;

        public void Setup(GachaLineupDialogViewModel viewModel)
        {
            _normalRatioPageComponent.Setup(viewModel.NormalRatioPageViewModel);
            _ssrRatioPageComponent.Setup(viewModel.SSRRatioPageViewModel);
            _urRatioPageComponent.Setup(viewModel.URRatioPageViewModel);
            _pickupRatioPageComponent.Setup(viewModel.PickupRatioPageViewModel);
        }

        public void SwitchGachaRatioPage(GachaRatioTabType type)
        {
            switch (type)
            {
                case GachaRatioTabType.NormalRatioTab:
                    _normalRatioPageComponent.Hidden = false;
                    _ssrRatioPageComponent.Hidden = true;
                    _urRatioPageComponent.Hidden = true;
                    _pickupRatioPageComponent.Hidden = true;
                    break;
                case GachaRatioTabType.SSRRatioTab:
                    _normalRatioPageComponent.Hidden = true;
                    _ssrRatioPageComponent.Hidden = false;
                    _urRatioPageComponent.Hidden = true;
                    _pickupRatioPageComponent.Hidden = true;
                    break;
                case GachaRatioTabType.URRatioTab:
                    _normalRatioPageComponent.Hidden = true;
                    _ssrRatioPageComponent.Hidden = true;
                    _urRatioPageComponent.Hidden = false;
                    _pickupRatioPageComponent.Hidden = true;
                    break;
                case GachaRatioTabType.PickupRatioTab:
                    _normalRatioPageComponent.Hidden = true;
                    _ssrRatioPageComponent.Hidden = true;
                    _urRatioPageComponent.Hidden = true;
                    _pickupRatioPageComponent.Hidden = false;
                    break;
            }
        }
    }
}
