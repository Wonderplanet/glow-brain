using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Views.RotationBanner.HomeMain;
using UIKit;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Views.HomeMainBanner
{
    public class HomeMainBannerItemViewController : UIViewController<HomeMainBannerItemView>
    {
        [Inject] IHomeMainBannerItemViewDelegate ViewDelegate { get; }

        HomeMainBannerItemViewModel _viewModel;

        public void SetViewModel(HomeMainBannerItemViewModel viewModel)
        {
            _viewModel = viewModel;

           UIBannerLoaderEx.Main.LoadBannerWithFadeIfNotLoaded(ActualView.BannerImage, HomeBannerAssetPath.CreateAssetPath(viewModel.AssetKey).Value);
        }

        [UIAction]
        public void OnBannerClicked()
        {
            ViewDelegate.OnBannerClicked(_viewModel.DestinationType, _viewModel.DestinationPath);
        }
    }
}
