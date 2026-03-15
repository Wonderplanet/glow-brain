using GLOW.Scenes.EncyclopediaEffectDialog.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.EncyclopediaEffectDialog.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-1_図鑑
    /// 　　91-1-3_キャラ図鑑ランク
    /// 　　　91-1-3-2_発動中の図鑑効果ダイアログ
    /// </summary>
    public class EncyclopediaEffectDialogViewController : UIViewController<EncyclopediaEffectDialogView>
    {
        [Inject] IEncyclopediaEffectDialogViewDelegate ViewDelegate { get; }
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void Setup(EncyclopediaEffectDialogViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        [UIAction]
        void BackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }
    }
}
