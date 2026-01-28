using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.AnnouncementWindow.Domain.Enum;
using GLOW.Scenes.AnnouncementWindow.Domain.ValueObject;
using GLOW.Scenes.AnnouncementWindow.Presentation.View;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.Facade
{
    public class AnnouncementViewFacade : IAnnouncementViewFacade
    {
        [Inject] IViewFactory ViewFactory { get; }

        async UniTask IAnnouncementViewFacade.ShowLoginAnnouncement(
            UIViewController parent, 
            CancellationToken cancellationToken)
        {
            var completionSource = new UniTaskCompletionSource();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());

            var argument = new AnnouncementMainViewController.Argument(AnnouncementDisplayMeansType.Login);
            var controller = ViewFactory.Create<AnnouncementMainViewController, AnnouncementMainViewController.Argument>(argument);
            controller.OnCloseCompletion = (_) =>
            {
                completionSource.TrySetResult();
            };

            parent.PresentModally(controller);
            await completionSource.Task;
        }

        void IAnnouncementViewFacade.ShowMenuAnnouncement(
            UIViewController parent, 
            Action<AlreadyReadAnnouncementFlag> onCloseCompletion)
        {
            var argument = new AnnouncementMainViewController.Argument(AnnouncementDisplayMeansType.Menu);
            var controller = ViewFactory.Create<AnnouncementMainViewController, AnnouncementMainViewController.Argument>(argument);
            controller.OnCloseCompletion = onCloseCompletion;
            parent.PresentModally(controller);
            
        }
    }
}
