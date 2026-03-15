using System;
using GLOW.Core.Presentation.Modules.Audio;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.HomeMainKomaSettingFilter.Presentation
{
    public class HomeMainKomaSettingFilterViewController :
        UIViewController<HomeMainKomaSettingFilterView>,
        IEscapeResponder
    {
        [Inject] IHomeMainKomaSettingFilterViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public Action OnCancelAction { get; set; }
        public Action OnConfirmAction { get; set; }

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

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public void InitializeView(HomeMainKomaSettingFilterViewModel viewModel)
        {
            ActualView.InitializeView(viewModel);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden)
            {
                return false;
            }

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            ViewDelegate.OnCancel();
            return true;
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCancel();
        }

        [UIAction]
        void OnConfirmButtonTapped()
        {
            ViewDelegate.OnConfirm();
        }
    }
}
