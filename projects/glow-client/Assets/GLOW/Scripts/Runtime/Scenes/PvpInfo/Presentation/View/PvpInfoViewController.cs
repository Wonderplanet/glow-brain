using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PvpInfo.Presentation.Presenter;
using GLOW.Scenes.PvpInfo.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.PvpInfo.Presentation.View
{
    public class PvpInfoViewController : UIViewController<PvpInfoView>
    {
        public record Argument(ContentSeasonSystemId SysPvpSeasonId);
        [Inject] IPvpInfoViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }

        public void Setup(PvpInfoViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped();
        }
    }
}
