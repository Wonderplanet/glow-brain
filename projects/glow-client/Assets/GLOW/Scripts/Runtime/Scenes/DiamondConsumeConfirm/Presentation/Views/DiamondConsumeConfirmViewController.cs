using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.DiamondConsumeConfirm.Domain.Enumerable;
using GLOW.Scenes.DiamondConsumeConfirm.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.DiamondConsumeConfirm.Presentation.Views
{
    /// <summary>
    /// 131_共通
    /// 　131-3_購入確認ダイアログ
    /// 　　131-5-1_購入確認ダイアログ（アプリ専用通貨 主に一次通貨）
    /// 　　131-3-4_アプリ専用通貨不足時ダイアログ
    /// 
    /// DiamondBuyConfirmのバリアント
    /// 汎用的な一次通貨消費確認ダイアログ
    /// </summary>
    public class DiamondConsumeConfirmViewController : UIViewController<DiamondConsumeConfirmView>
    {
        public record Argument(TotalDiamond ConsumeDiamond, ConsumeType Type, Action OnConfirm, Action OnCancel);
        [Inject] IDiamondConsumeConfirmViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.ViewDidLoad();
        }

        public void Setup(DiamondConsumeConfirmViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        [UIAction]
        void SpecificCommerceButtonTapped()
        {
            ViewDelegate.SpecificCommerceButtonTapped();
        }


        [UIAction]
        void ConfirmButtonTapped()
        {
            ViewDelegate.ConfirmButtonTapped();
        }

        [UIAction]
        void CancelButtonTapped()
        {
            ViewDelegate.CancelButtonTapped();
        }
    }
}
