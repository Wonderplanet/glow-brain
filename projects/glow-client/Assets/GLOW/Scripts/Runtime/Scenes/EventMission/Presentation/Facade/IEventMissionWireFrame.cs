using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using UIKit;

namespace GLOW.Scenes.EventMission.Presentation.Facade
{
    public interface IEventMissionWireFrame
    {
        void ShowEventMissionViewInEvent(
            UIViewController parent,
            MasterDataId mstEventId,
            Action onCloseCompletion);

        UniTask<MissionClosedByChallengeFlag> ShowEventMissionViewInHome(
            UIViewController parent,
            MissionType missionType,
            MasterDataId mstEventId,
            Action onCloseCompletion,
            CancellationToken cancellationToken);
    }
}
