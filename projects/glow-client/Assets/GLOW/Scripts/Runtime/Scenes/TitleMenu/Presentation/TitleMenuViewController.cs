using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.AnnouncementWindow.Domain.ValueObject;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.TitleMenu.Presentation
{
    public class TitleMenuViewController : UIViewController<TitleMenuView>, IEscapeResponder
    {
        public record Argument(AlreadyReadAnnouncementFlag AlreadyReadAnnouncementFlag);
        public Action<AlreadyReadAnnouncementFlag> OnClosedAction { private get; set; }
        
        [Inject] ITitleMenuViewDelegate TitleMenuViewDelegate { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            TitleMenuViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            // ModalContextに上書きされないようにここで登録
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }
        
        public void SetAnnouncementAlreadyAnnouncementBadge(AlreadyReadAnnouncementFlag flag)
        {
            ActualView.SetAnnouncementNotificationBadge(flag);
        }
        
        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden)
            {
                return false;
            }

            SystemSoundEffectProvider.PlaySeTap();
            OnClosedAction(ActualView.AlreadyReadAnnouncementFlag);
            
            Dismiss();
            return true;
        }

        [UIAction]
        void OnAnnouncement()
        {
            TitleMenuViewDelegate.OnAnnouncement();
        }

        [UIAction]
        void OnInquiry()
        {
            TitleMenuViewDelegate.OnInquiry();
        }

        [UIAction]
        void OnRepairData()
        {
            TitleMenuViewDelegate.OnRepairData();
        }

        [UIAction]
        void OnLinkAccount()
        {
            TitleMenuViewDelegate.OnLinkAccount();
        }

        [UIAction]
        void OnDeleteUserData()
        {
            TitleMenuViewDelegate.OnDeleteUserData();
        }

        [UIAction]
        void OnClose()
        {
            OnClosedAction(ActualView.AlreadyReadAnnouncementFlag);
            Dismiss();
        }
    }
}
