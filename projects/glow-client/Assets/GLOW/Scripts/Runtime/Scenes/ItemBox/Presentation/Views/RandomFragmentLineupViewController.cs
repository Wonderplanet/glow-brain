using System.Collections.Generic;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ItemBox.Presentation.Views
{
    public class RandomFragmentLineupViewController : UIViewController<RandomFragmentLineupView>
    {
        public record Argument(IReadOnlyList<LineupFragmentViewModel> Lineup);

        [Inject] IRandomFragmentLineupViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            ViewDelegate.OnViewDidUnload();
        }

        public void Setup(IReadOnlyList<LineupFragmentViewModel> lineup)
        {
            ActualView.Setup(lineup);
        }

        [UIAction]
        public void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseSelected();
        }
    }
}
