using System;
using GLOW.Scenes.InGame.Presentation.Presenters;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Views.InGamePause
{
    public class InGamePauseViewController : UIViewController<InGamePauseView>, IEscapeResponder
    {
        public record Argument(Action<UIViewController> OnClose);
        
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] Argument Arg { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);
        }

        void Close()
        {
            Arg.OnClose?.Invoke(this);
            Dismiss(animated: false);
        }

        bool IEscapeResponder.OnEscape()
        {
            if(ActualView.Hidden) return false;

            Close();
            return true;
        }

        [UIAction]
        void OnClose()
        {
            Close();
        }
    }
}
