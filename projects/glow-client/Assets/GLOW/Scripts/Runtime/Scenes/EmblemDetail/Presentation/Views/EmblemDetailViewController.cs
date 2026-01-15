using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EmblemDetail.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.EmblemDetail.Presentation.Views
{
    /// <summary>
    /// エンブレム詳細ダイアログ
    /// </summary>
    public class EmblemDetailViewController : UIViewController<EmblemDetailView>
    {
        public record Argument(MasterDataId MstEmblemId);

        [Inject] IEmblemDetailViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void SetUp(EmblemDetailViewModel viewModel)
        {
            ActualView.SetUp(viewModel);
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped();
        }
    }
}
