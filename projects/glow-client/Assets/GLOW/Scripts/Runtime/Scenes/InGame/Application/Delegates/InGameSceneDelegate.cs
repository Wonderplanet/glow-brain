using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.InGame.Presentation.Views;
using UIKit;
using WonderPlanet.SceneManagement;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Application.SceneDelegates;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.InGame.Application.Delegates
{
    public class InGameSceneDelegate : AbstractSceneDelegate, IOverwrapTransitionDelegate
    {
        [Inject] UICanvas Canvas { get; }
        [Inject] IViewFactory ViewFactory { get; }

        InGameViewController _inGameViewController;

        public override async UniTask Initialize(CancellationToken cancellationToken)
        {
            await base.Initialize(cancellationToken);

            ApplicationLog.Log(nameof(InGameSceneDelegate), nameof(IInitializable.Initialize));
        }

        public override void SceneWillAppear()
        {
            base.SceneWillAppear();

            _inGameViewController = ViewFactory.Create<InGameViewController>();
            Canvas.RootViewController.Show(_inGameViewController, false); 
        }

        public override void SceneDidDisappear()
        {
            base.SceneDidDisappear();
        }

        public void OnDestinationSceneReady(IOverwrapTransitionTrigger trigger)
        {
            DoAsync.Invoke(this.GetCancellationTokenOnDestroy(), async cancellationToken =>
            {
                await UniTask.WaitUntil(
                    () => _inGameViewController != null && _inGameViewController.IsInitialized, 
                    cancellationToken: cancellationToken);

                trigger.TransitionComplete();
            });
        }
    }
}
