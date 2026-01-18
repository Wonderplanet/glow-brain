using GLOW.Core.Presentation.Components;
using GLOW.Scenes.DiamondConsumeConfirm.Presentation.ViewModels;
using GLOW.Scenes.ShopBuyConform.Presentation.Component;
using UIKit;
using UnityEngine;

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
    public class DiamondConsumeConfirmView : UIView
    {
        [SerializeField] UIText _titleText;
        [SerializeField] UIText _messageText;
        [SerializeField] UIText _confirmButtonText;
        [SerializeField] UIObject _diamondShortageText;
        [SerializeField] UseResourceAmountChangeDisplayComponent _paidUseResourceAmountChangeDisplayComponent;
        [SerializeField] UseResourceAmountChangeDisplayComponent _freeUseResourceAmountChangeDisplayComponent;

        public void Setup(DiamondConsumeConfirmViewModel viewModel)
        {
            _titleText.SetText(viewModel.Text.Title);
            _messageText.SetText(viewModel.Text.Message);
            _confirmButtonText.SetText(viewModel.Text.ConfirmButton);
            _diamondShortageText.IsVisible = !viewModel.IsEnoughDiamond;
            _paidUseResourceAmountChangeDisplayComponent.SetupPaidDiamondAmount(viewModel.CurrentPaidDiamond, viewModel.AfterPaidDiamond);
            _freeUseResourceAmountChangeDisplayComponent.SetupFreeDiamondAmount(viewModel.CurrentFreeDiamond, viewModel.AfterFreeDiamond);
        }
    }
}
