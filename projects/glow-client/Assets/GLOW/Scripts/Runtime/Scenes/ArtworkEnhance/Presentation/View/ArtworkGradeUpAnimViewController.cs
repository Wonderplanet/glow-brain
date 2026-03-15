using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public class ArtworkGradeUpAnimViewController : UIViewController<ArtworkGradeUpAnimView>
    {
        public record Argument(MasterDataId MstArtworkId);
        [Inject] IArtworkGradeUpAnimDelegate ViewDelegate;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void Setup(ArtworkGradeUpAnimViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        public void AnimationEnded()
        {
            ActualView.AnimationEnded();
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }
    }
}
