using GLOW.Core.Presentation.Components;
using GLOW.Scenes.FragmentProvisionRatio.Presentation.DestinationBanner;
using GLOW.Scenes.GachaRatio.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.GachaRatio.Presentation.Views.Components
{
    public class GachaRatioPageComponent : UIObject
    {
        [SerializeField] RatioByRarityComponent _ratioComponent;
        [SerializeField] GachaRatioLineupListComponent _pickupComponent;
        [SerializeField] GachaRatioLineupListComponent _lineupComponent;

        public void Setup(GachaRatioPageViewModel viewModel)
        {
            if (viewModel.IsEmpty()) return;

            _ratioComponent.SetModel(
                viewModel.ByRarityViewModel.UR.Rarity,
                viewModel.ByRarityViewModel.UR.OutputRatio);
            _ratioComponent.SetModel(
                viewModel.ByRarityViewModel.SSR.Rarity,
                viewModel.ByRarityViewModel.SSR.OutputRatio);
            _ratioComponent.SetModel(
                viewModel.ByRarityViewModel.SR.Rarity,
                viewModel.ByRarityViewModel.SR.OutputRatio);
            _ratioComponent.SetModel(
                viewModel.ByRarityViewModel.R.Rarity,
                viewModel.ByRarityViewModel.R.OutputRatio);

            _pickupComponent.Setup(viewModel.GachaRatioPickupListViewModel);
            _lineupComponent.Setup(viewModel.GachaRatioLineupListViewModel);
        }
    }
}
