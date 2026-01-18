using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ViewModels.InGameUnitDetail;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Views.InGameUnitDetail
{
    public class InGameUnitDetailViewController : UIViewController<InGameUnitDetailView>, IEscapeResponder
    {
        public record Argument(UserDataId UserUnitId);

        [Inject] IInGameUnitDetailViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }

        public Action OnClosed { get; set; }
        public bool IsPlayingTutorial{ get; set; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();

            ViewDelegate.ViewDidAppear();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public void Setup(InGameUnitDetailViewModel viewModel, BattleStateEffectViewManager battleStateEffectViewManager)
        {
            ActualView.Setup(viewModel, battleStateEffectViewManager);
            ActualView.SetCloseAction(Close);
        }

        public void Close()
        {
            if (IsPlayingTutorial) return;

            ViewDelegate.OnClosed();
            Dismiss(completion:() =>
            {
                OnClosed?.Invoke();
            });
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            SystemSoundEffectProvider.PlaySeTap();
            Close();

            return true;
        }
    }
}
