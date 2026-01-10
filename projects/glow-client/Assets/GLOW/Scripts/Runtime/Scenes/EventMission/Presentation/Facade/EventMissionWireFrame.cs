using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.EventMission.Presentation.View.EventMissionMain;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EventMission.Presentation.Facade
{
    public class EventMissionWireFrame : IEventMissionWireFrame
    {
        [Inject] IViewFactory ViewFactory { get; }

        void IEventMissionWireFrame.ShowEventMissionViewInEvent(
            UIViewController parent,
            MasterDataId mstEventId,
            Action onCloseCompletion)
        {
            var argument = new EventMissionMainViewController.Argument(false, false, mstEventId);
            var controller =
                ViewFactory.Create<EventMissionMainViewController, EventMissionMainViewController.Argument>(argument);
            controller.OnCloseCompletion = () =>
            {
                onCloseCompletion?.Invoke();
            };
            parent.PresentModally(controller);
        }

        async UniTask<MissionClosedByChallengeFlag> IEventMissionWireFrame.ShowEventMissionViewInHome(
            UIViewController parent,
            MissionType missionType,
            MasterDataId mstEventId,
            Action onCloseCompletion,
            CancellationToken cancellationToken)
        {
            var completionSource = new UniTaskCompletionSource<MissionClosedByChallengeFlag>();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());

            var argument = new EventMissionMainViewController.Argument(
                missionType == MissionType.EventDailyBonus,
                true,
                mstEventId);

            var controller =
                ViewFactory.Create<EventMissionMainViewController, EventMissionMainViewController.Argument>(argument);

            controller.OnCloseCompletion = () =>
            {
                onCloseCompletion?.Invoke();
                completionSource.TrySetResult(MissionClosedByChallengeFlag.False);
            };
            controller.OnDismissByChallenge = () =>
            {
                completionSource.TrySetResult(MissionClosedByChallengeFlag.True);
            };

            parent.PresentModally(controller);
            return await completionSource.Task;
        }
    }
}
