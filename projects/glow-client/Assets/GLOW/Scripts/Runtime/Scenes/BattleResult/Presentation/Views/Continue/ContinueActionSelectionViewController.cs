using System;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-2_敗北リザルト
    /// 　　53-2-1-2_コンティニュー
    /// 　　　53-2-1-2-1_コンティニュー確認ダイアログ
    /// </summary>
    public class ContinueActionSelectionViewController : UIViewController<ContinueActionSelectionView>
    {
        public enum Result
        {
            Cancel,
            Continue,
            QuestPeriodOutside // クエスト期間外
        }

        public record Argument(
            ContinueActionSelectionViewModel ViewModel,
            Action<Result> OnViewClosed);

        [Inject] IContinueActionSelectionViewDelegate ViewDelegate { get; }

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

        public void SetUp(ContinueActionSelectionViewModel viewModel)
        {
            ActualView.SetUp(viewModel);
        }

        [UIAction]
        void OnCancelTapped()
        {
            ViewDelegate.OnCancelSelected();
        }

        [UIAction]
        void OnContinueDiamondTapped()
        {
            ViewDelegate.OnContinueDiamondSelected();
        }

        [UIAction]
        void OnContinueAdTapped()
        {
            ViewDelegate.OnContinueAdSelected();
        }
    }
}
