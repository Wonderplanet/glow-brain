using System;
using GLOW.Core.Domain.ValueObjects;
using UIKit;
using GLOW.Scenes.Login.Presentation.ViewModels;
using GLOW.Scenes.Title.Presentations.ViewModels;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Title.Presentations.Views
{
    public sealed class TitleViewController : UIViewController<TitleView>, IEscapeResponder
    {
        [Inject] ITitleViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }

        LoginPhaseViewModel _loginPhaseViewModel;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            ViewDelegate.OnViewDidUnload();
        }

        public void SetApplicationInfo(ApplicationInfoViewModel viewModel)
        {
            ActualView.SetApplicationVersion(viewModel.ApplicationVersion.Value);
        }

        public void SetUserMyId(UserMyId id)
        {
            ActualView.SetUserMyIdVisible(!id.IsEmpty());
            ActualView.SetUserMyIdText(id);
        }

        public void SetProgress(LoginProgressViewModel viewModel)
        {
            ActualView.SetProgress(viewModel.Progress);
        }

        public void SetLoginPhase(LoginPhaseViewModel viewModel)
        {
            _loginPhaseViewModel = viewModel;
            ActualView.SetLoginPhase(viewModel.Message);
        }

        public void SetOnTouchLayerTouched(Action touchCallback)
        {
            ActualView.OnTouch = touchCallback;
        }

        public void SetMenuButtonNotificationBadge(NotificationBadge badge)
        {
            ActualView.SetMenuButtonNotificationBadge(badge);
        }

        public void OnEndLoading()
        {
            ActualView.EndLoading();
        }
        
        public void PlayInAnimation()
        {
            ActualView.PlayInAnimation();
        }

        bool IEscapeResponder.OnEscape()
        {
            // NOTE: 表示してない状態であれば反応しないように制御
            if (ActualView.Hidden)
            {
                return false;
            }

            // NOTE: タップ音を鳴らす
            SystemSoundEffectProvider.PlaySeTap();
            ViewDelegate.OnEscapeSelected(_loginPhaseViewModel.LoginPhase);
            // NOTE: ハンドリングをおこなったのでtrueを返す
            return true;
        }

        [UIAction]
        void OnClickMenuButton()
        {
            ViewDelegate.OnMenuSelected();
        }
    }
}
