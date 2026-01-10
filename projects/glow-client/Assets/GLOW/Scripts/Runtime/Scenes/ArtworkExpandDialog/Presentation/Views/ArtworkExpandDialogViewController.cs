using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.ValueObject;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ArtworkExpandDialog.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-4_作品別原画表示
    /// 　　91-4-1_原画拡大ダイアログ
    /// </summary>
    public class ArtworkExpandDialogViewController : UIViewController<ArtworkExpandDialogView>
    {
        public record Argument(MasterDataId MstArtworkId ,ArtworkDetailDisplayType ArtworkDetailDisplayType);

        [Inject] IArtworkExpandDialogViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void SetUpFromEncyclopedia(ArtworkExpandDialogViewModel viewModel)
        {
            ActualView.SetUpFromEncyclopedia(viewModel);
        }

        public void SetUpFromExchangeShop(ArtworkExpandDialogViewModel viewModel, bool isLock)
        {
            ActualView.SetUpFromExchangeShop(viewModel, isLock);
        }

        [UIAction]
        void CloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped();
        }
    }
}
