using System;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Constants.Zenject;
using WPFramework.Modules.Log;
using Zenject;

namespace WPFramework.Presentation.InteractionControls
{
    public class ScreenInteractionControl<T> : IScreenInteractionControl where T : UIViewController, new()
    {
        [Inject(Id = FrameworkInjectId.Canvas.System)] UICanvas Canvas { get; }
        [Inject] Context Context { get; }

        T _interactionViewController;
        int _lockCount;

        void IAsyncActivityControl.ActivityBegin()
        {
            if (_lockCount > 0)
            {
                ++_lockCount;
                return;
            }
            
            _interactionViewController = new T();
            // NOTE: ViewController を new で生成しているため注入でを行うために Inject で注入する
            Context.Container.Inject(_interactionViewController);
            Canvas.RootViewController.Show(_interactionViewController);
            ++_lockCount;

            ApplicationLog.Log(nameof(ScreenInteractionControl<T>), "ActivityBegin");
        }

        void IAsyncActivityControl.ActivityEnd()
        {
            --_lockCount;
            if (_lockCount > 0)
            {
                return;
            }

            _interactionViewController?.Dismiss();
            _interactionViewController = null;

            ApplicationLog.Log(nameof(ScreenInteractionControl<T>), "ActivityEnd");
        }

        IDisposable IScreenInteractionControl.Lock()
        {
            return this.Activate();
        }

        void IScreenInteractionControl.Disable()
        {
            if (_interactionViewController != null)
            {
                _interactionViewController.View.Hidden = true;
            }

            ApplicationLog.Log(nameof(ScreenInteractionControl<T>), "Hide");
        }

        void IScreenInteractionControl.Enable()
        {
            if (_interactionViewController != null && _interactionViewController.View.Hidden)
            {
                _interactionViewController.View.Hidden = false;
            }

            ApplicationLog.Log(nameof(ScreenInteractionControl<T>), "Show");
        }
    }
}
