using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.UnitReceive.Presentation.ViewModel;
using UIKit;
using WonderPlanet.ResourceManagement;
using Zenject;

namespace GLOW.Scenes.UnitReceive.Presentation.View
{
    public class UnitReceiveViewController : UIViewController<UnitReceiveView>
    {
        public record Argument(UnitReceiveViewModel ViewModel);

        [Inject] IUnitReceiveViewDelegate ViewDelegate { get; }
        [Inject] IAssetSource AssetSource { get; }

        public Action OnCloseCompletion { get; set; }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
        }

        public void SetUpView(
            UnitReceiveViewModel viewModel)
        {
            ActualView.SetUp(viewModel, AssetSource);
        }

        public async UniTask PlayOpenAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayOpenAnimation(cancellationToken);
        }

        public async UniTask PlayCloseAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayCloseAnimation(cancellationToken);
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped(OnCloseCompletion);
        }
    }
}
