using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaEmblemDetail.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.EncyclopediaEmblemDetail.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-5_作品別エンブレム表示
    /// 　　91-5-1エンブレム詳細ダイアログ
    /// </summary>
    public class EncyclopediaEmblemDetailViewController : UIViewController<EncyclopediaEmblemDetailView>
    {
        public record Argument(MasterDataId MstEmblemId);

        [Inject] IEncyclopediaEmblemDetailViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void Setup(EncyclopediaEmblemDetailViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped();
        }

        [UIAction]
        void OnAssignEmblemButtonTapped()
        {
            ViewDelegate.OnAssignEmblemButtonTapped();
        }
    }
}
