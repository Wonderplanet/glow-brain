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
    /// 　　　53-2-1-2-2_コンティニューダイアログ（プリズム）
    /// </summary>
    public class ContinueDiamondViewController : UIViewController<ContinueDiamondView>
    {
        public enum Result
        {
            Cancel,
            Continue,
            Purchase,
            QuestPeriodOutside // クエスト期間外
        }

        public record Argument(
            ContinueDiamondViewModel ViewModel,
            Action<Result> OnViewClosed);

        [Inject] IContinueDiamondViewDelegate ViewDelegate { get; }

        public Action OnCancel { get; set; }

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

        public void SetUp(ContinueDiamondViewModel diamondViewModel)
        {
            ActualView.SetUp(diamondViewModel);
        }

        [UIAction]
        void OnCancelTapped()
        {
            ViewDelegate.OnCancelSelected();
        }

        [UIAction]
        void OnSpecificCommerceTapped()
        {
            ViewDelegate.OnSpecificCommerceSelected();
        }

        [UIAction]
        void OnContinueDiamondTapped()
        {
            ViewDelegate.OnContinueDiamondSelected();
        }

        [UIAction]
        void OnPurchaseTapped()
        {
            ViewDelegate.OnPurchaseSelected();
        }
    }
}
