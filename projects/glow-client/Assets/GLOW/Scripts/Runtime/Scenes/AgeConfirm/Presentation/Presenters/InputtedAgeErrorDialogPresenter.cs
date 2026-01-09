using GLOW.Scenes.AgeConfirm.Presentation.View;
using Zenject;

namespace GLOW.Scenes.AgeConfirm.Presentation.Presenters
{
    /// <summary>
    /// 74-1_年齢確認
    /// 　74-1-1-1_年齢入力エラーダイアログ
    /// </summary>
    public class InputtedAgeErrorDialogPresenter : IInputtedAgeErrorDialogViewDelegate
    {
        [Inject] InputtedAgeErrorDialogViewController ViewController { get; }
        public void OnCloseButtonClicked()
        {
            ViewController.Dismiss();
        }
    }
}
