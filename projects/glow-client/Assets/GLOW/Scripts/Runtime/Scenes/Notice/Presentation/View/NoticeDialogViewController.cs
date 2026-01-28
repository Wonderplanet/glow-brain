using System;
using GLOW.Scenes.Notice.Presentation.ViewModel;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Notice.Presentation.View
{
    /// <summary>
    /// 12-3_ノーティス
    /// </summary>
    public class NoticeDialogViewController : 
        UIViewController<NoticeDialogView>,
        IEscapeResponder
    {
        public record Argument(NoticeViewModel ViewModel);

        public Action OnCloseCompletion { get; set; }
        public Action OnTransitCompletion { get; set; }

        [Inject] INoticeDialogViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }
        
        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
            
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public void SetViewModel(NoticeViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }
        
        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;
            
            SystemSoundEffectProvider.PlaySeEscape();
            ViewDelegate.OnCloseSelected();
            return true;
        }
        
        [UIAction]
        void OnCloseSelected()
        {
            ViewDelegate.OnCloseSelected();
        }
        
        [UIAction]
        void OnTransitSelected()
        {
            ViewDelegate.OnTransitSelected();
        }
    }
}