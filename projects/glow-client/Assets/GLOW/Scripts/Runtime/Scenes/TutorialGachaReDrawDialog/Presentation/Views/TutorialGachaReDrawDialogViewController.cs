using System;
using UIKit;
using Zenject;

namespace GLOW.Scenes.TutorialGachaReDrawDialog.Presentation.Views
{
    public class TutorialGachaReDrawDialogViewController : UIViewController<TutorialGachaReDrawDialogView>
    {
        public record Argument(Action TutorialGachaReDrawAction, Action LineupAction);
        
        [Inject] ITutorialGachaReDrawDialogViewDelegate Delegate { get; }
        
        [UIAction]
        void OnReDrawButtonTapped()
        {
            Delegate.OnReDrawButtonTapped();
        }
        
        [UIAction]
        void OnLineupButtonTapped()
        {
            Delegate.OnLineupButtonTapped();
        }
        
        [UIAction]
        void OnCancelButtonTapped()
        {
            Dismiss();
        }
    }
}