using GLOW.Scenes.PvpPreviousSeasonResult.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.PvpPreviousSeasonResult.Presentation.Views
{
    public class PvpPreviousSeasonResultViewController : UIViewController<PvpPreviousSeasonResultView>
    {
        public record Argument(PvpPreviousSeasonResultViewModel ViewModel);
        [Inject] IPvpPreviousSeasonResultViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.ViewDidLoad();
        }

        [UIAction]
        public void OnCloseButtonClicked()
        {
            ViewDelegate.OnCloseButtonClicked();
        }

        public void SetUp(PvpPreviousSeasonResultViewModel viewModel)
        {
            ActualView.SetUp(viewModel);
        }
    }
}
