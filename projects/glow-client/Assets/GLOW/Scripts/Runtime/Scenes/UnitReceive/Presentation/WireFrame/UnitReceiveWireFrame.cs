using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitReceive.Domain.UseCase;
using GLOW.Scenes.UnitReceive.Presentation.Translator;
using GLOW.Scenes.UnitReceive.Presentation.View;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UnitReceive.Presentation.WireFrame
{
    public class UnitReceiveWireFrame : IUnitReceiveWireFrame
    {
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] DisplayReceivedUnitUseCase DisplayReceivedUnitUseCase { get; }
        
        void IUnitReceiveWireFrame.ShowReceivedUnit(MasterDataId receivedUnitId, UIViewController parentViewController)
        {
            var model = DisplayReceivedUnitUseCase.GetReceivedUnitInfo(receivedUnitId);
            if (model.IsEmpty()) return;
            
            var viewModel = UnitReceiveViewModelTranslator.ToReceiveViewModel(model);
            var argument = new UnitReceiveViewController.Argument(viewModel);
            
            var unitReceiveViewController = ViewFactory.Create<
                UnitReceiveViewController, 
                UnitReceiveViewController.Argument>
                (argument);
            parentViewController.Show(unitReceiveViewController);
        }

        async UniTask IUnitReceiveWireFrame.ShowReceivedUnits(
            IReadOnlyList<MasterDataId> receivedUnitIds, 
            UIViewController parentViewController, 
            CancellationToken cancellationToken)
        {
            foreach (var id in receivedUnitIds)
            {
                await ShowReceivedUnitAsync(id, parentViewController, cancellationToken);
            }
        }
        
        async UniTask ShowReceivedUnitAsync(
            MasterDataId receivedUnitId,
            UIViewController parentViewController,
            CancellationToken cancellationToken)
        {
            var completionSource = new UniTaskCompletionSource();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());
            
            var model = DisplayReceivedUnitUseCase.GetReceivedUnitInfo(receivedUnitId);
            if (model.IsEmpty()) return;
            
            var viewModel = UnitReceiveViewModelTranslator.ToReceiveViewModel(model);
            
            var argument = new UnitReceiveViewController.Argument(viewModel);
            var controller = ViewFactory.Create<
                UnitReceiveViewController, 
                UnitReceiveViewController.Argument>
                (argument);
            
            controller.OnCloseCompletion = () =>
            {
                completionSource.TrySetResult();
            };
            
            parentViewController.Show(controller);
            await completionSource.Task;
        }
    }
}