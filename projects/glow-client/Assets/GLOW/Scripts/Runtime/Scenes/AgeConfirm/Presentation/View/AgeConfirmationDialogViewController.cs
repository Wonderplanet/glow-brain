using System;
using UIKit;
using Zenject;

namespace GLOW.Scenes.AgeConfirm.Presentation.View
{
    /// <summary>
    /// 74-1_年齢確認
    /// </summary>
    public class AgeConfirmationDialogViewController : UIViewController<AgeConfirmationDialogView>
    {
        [Inject] IAgeConfirmationDialogViewDelegate Delegate { get; }
        
        public Action OnAgeConfirmEnded { get; set; }
        public Action OnAgeConfirmCanceled { get; set; }
        
        [UIAction]
        void OnOkButton()
        {
            // 生年月日の確認ダイアログを閉じ、入力された生年月日の確認ダイアログを表示
            Delegate.OnOKButtonTapped();
        }

        [UIAction]
        void OnCancelButton()
        {
            // 生年月日の確認ダイアログを閉じる
            Delegate.OnCloseButtonTapped();
        }

        [UIAction]
        void OnSpecificCommerceButton()
        {
            // 特定商取引法に基づく表示を行う
            Delegate.OnTermsOfService();
        }
    }
}
