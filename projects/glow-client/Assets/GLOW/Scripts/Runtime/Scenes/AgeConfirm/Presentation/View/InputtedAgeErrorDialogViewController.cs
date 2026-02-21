using UIKit;
using Zenject;

namespace GLOW.Scenes.AgeConfirm.Presentation.View
{
    /// <summary>
    /// 74-1_年齢確認
    /// 　74-1-1-1_年齢入力エラーダイアログ
    /// </summary>
    public class InputtedAgeErrorDialogViewController : UIViewController<InputtedAgeErrorDialogView>
    {
        [Inject] IInputtedAgeErrorDialogViewDelegate Delegate { get; }

        [UIAction]
        void OnCloseButton()
        {
            Delegate.OnCloseButtonClicked();
        }
    }
}
