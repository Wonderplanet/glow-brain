using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaRatio.Domain.Constants;
using GLOW.Scenes.GachaRatio.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.GachaRatio.Presentation.Views.Components
{
    public class GachaRatioPageRootComponent : UIObject
    {
        [SerializeField] GachaRatioPageComponent _normalRatioPageComponent;
        [SerializeField] GachaRatioPageComponent _ssrRatioPageComponent;
        [SerializeField] GachaRatioPageComponent _urRatioPageComponent;
        [SerializeField] GachaRatioPageComponent _pickupRatioPageComponent;

        public void Setup(GachaRatioDialogViewModel viewModel)
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
