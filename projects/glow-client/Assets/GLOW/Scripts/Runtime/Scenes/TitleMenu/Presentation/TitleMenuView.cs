using GLOW.Core.Presentation.Components;
using GLOW.Scenes.AnnouncementWindow.Domain.ValueObject;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.TitleMenu.Presentation
{
    public class TitleMenuView : UIView
    {
        [SerializeField] UIImage _announcementBadge;
        
        public AlreadyReadAnnouncementFlag AlreadyReadAnnouncementFlag => new (_announcementBadge.Hidden);
        
        public void SetAnnouncementNotificationBadge(AlreadyReadAnnouncementFlag flag)
        {
            _announcementBadge.Hidden = flag;
        }
    }
}
