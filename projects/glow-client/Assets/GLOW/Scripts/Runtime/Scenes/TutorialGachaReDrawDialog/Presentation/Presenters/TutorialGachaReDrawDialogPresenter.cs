using GLOW.Scenes.TutorialGachaReDrawDialog.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.TutorialGachaReDrawDialog.Presentation.Presenters
{
    public class TutorialGachaReDrawDialogPresenter : ITutorialGachaReDrawDialogViewDelegate
    {
        [Inject] TutorialGachaReDrawDialogViewController.Argument Argument { get; }
        [Inject] TutorialGachaReDrawDialogViewController ViewController { get; }

        void ITutorialGachaReDrawDialogViewDelegate.OnReDrawButtonTapped()
        {
            // ガシャの引き直しのためtrueを渡す
            Argument.TutorialGachaReDrawAction?.Invoke();
            ViewController.Dismiss();
        }

        void ITutorialGachaReDrawDialogViewDelegate.OnLineupButtonTapped()
        {
            // ラインナップを閉じた際の際表示処理はGachaResultからArgument経由で渡すLineupAction内に含む
            Argument.LineupAction?.Invoke();
            ViewController.Dismiss();
        }
    }
}