using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using UIKit;

namespace GLOW.Scenes.UnitReceive.Presentation.WireFrame
{
    public interface IUnitReceiveWireFrame
    {
        void ShowReceivedUnit(MasterDataId receivedUnitId, UIViewController parentViewController);

        UniTask ShowReceivedUnits(
            IReadOnlyList<MasterDataId> receivedUnitIds,
            UIViewController parentViewController,
            CancellationToken cancellationToken);
    }
}