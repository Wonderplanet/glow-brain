using GLOW.Scenes.AppAppliedBalanceDialog.Domain;
using UIKit;
using Zenject;

namespace GLOW.Scenes.AppAppliedBalanceDialog.Presentation
{
    /// <summary>
    /// 121_メニュー
    /// 　121-8_その他
    /// 　　121-8-8_アプリ専用通貨残高確認
    /// </summary>
    public class AppAppliedBalanceDialogViewController : UIViewController<AppAppliedBalanceDialogView>
    {
        [Inject] GetAppAppliedBalanceUseCase AppAppliedBalanceUseCase { get; }
        [Inject] AppAppliedBalanceViewModelTranslator AppAppliedBalanceViewModelTranslator { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            var useCaseModel = AppAppliedBalanceUseCase.GetUseCaseModel();
            var viewModel = AppAppliedBalanceViewModelTranslator.TranslateViewModel(useCaseModel);
            ActualView.Initialize(viewModel);
        }

        [UIAction]
        void OnClose()
        {
            this.Dismiss();
        }
    }
}
