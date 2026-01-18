using GLOW.Scenes.PvpNewSeasonStart.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.PvpNewSeasonStart.Presentation.Views
{
    public class PvpNewSeasonStartViewController : UIViewController<PvpNewSeasonStartView>
    {
        public record Argument(PvpNewSeasonStartViewModel ViewModel);

        [Inject] IPvpNewSeasonStartViewDelegate ViewDelegate { get; }


        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.ViewDidLoad();
        }

        public void SetUp(PvpNewSeasonStartViewModel viewModel)
        {
            ActualView.SetUp(viewModel);
        }

        [UIAction]
        public void OnCloseButtonTapped()
        {
            ActualView.Interactable = false;
            ViewDelegate.OnCloseButtonTapped();
        }
    }
}
