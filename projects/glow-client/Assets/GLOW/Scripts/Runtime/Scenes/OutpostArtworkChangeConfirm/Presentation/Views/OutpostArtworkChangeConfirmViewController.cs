using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.OutpostArtworkChangeConfirm.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.OutpostArtworkChangeConfirm.Presentation.Views
{
    public class OutpostArtworkChangeConfirmViewController : UIViewController<OutpostArtworkChangeConfirmView>
    {
        public record Argument(MasterDataId MstArtworkId);

        [Inject] IOutpostArtworkChangeConfirmViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void Setup(OutpostArtworkChangeConfirmViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        [UIAction]
        void OnChangeArtworkButtonTapped()
        {
            ViewDelegate.OnChangeArtworkButtonTapped();
        }

        [UIAction]
        void OnChancelButtonTapped()
        {
            ViewDelegate.OnChancelButtonTapped();
        }
    }
}
