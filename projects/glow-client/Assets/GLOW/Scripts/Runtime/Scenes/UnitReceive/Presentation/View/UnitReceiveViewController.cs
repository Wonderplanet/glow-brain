using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.UnitReceive.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.UnitReceive.Presentation.View
{
    public class UnitReceiveViewController : UIViewController<UnitReceiveView>
    {
        public record Argument(UnitReceiveViewModel ViewModel);

        [Inject] IUnitReceiveViewDelegate ViewDelegate { get; }
        [Inject] IUnitImageContainer UnitImageContainer { get; }

        public Action OnCloseCompletion { get; set; }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
        }

        public void SetUpView(
            UnitReceiveViewModel viewModel)
        {
            var unitImage = InstantiateUnitImage(viewModel.UnitImageAssetPath);
            ActualView.SetUp(viewModel, unitImage);
        }

        public async UniTask PlayOpenAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayOpenAnimation(cancellationToken);
        }

        public async UniTask PlayCloseAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayCloseAnimation(cancellationToken);
        }

        UnitImage InstantiateUnitImage(UnitImageAssetPath imageAssetPath)
        {
            if (imageAssetPath.IsEmpty())
            {
                return null;
            }

            var go = UnitImageContainer.Get(imageAssetPath);
            var characterImage = go.GetComponent<UnitImage>();
            characterImage.SortingOrder = 0;
            return characterImage;
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped(OnCloseCompletion);
        }
    }
}
