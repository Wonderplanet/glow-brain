using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.AnnouncementWindow.Domain.ValueObject;
using UIKit;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.Facade
{
    public interface IAnnouncementViewFacade
    {
        UniTask ShowLoginAnnouncement(UIViewController parent, CancellationToken cancellationToken);
        void ShowMenuAnnouncement(UIViewController parent, Action<AlreadyReadAnnouncementFlag> onCloseCompletion = null);
    }
}
