using UIKit;
using WonderPlanet.ObservabilityKit;
using Zenject;

namespace WPFramework.Modules.Observability
{
    public class GameInteractionViewController : UIViewController
    {
        [Inject] IGameInteractionFactory GameInteractionFactory { get; }

        IGameInteraction _gameInteraction;

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            _gameInteraction = GameInteractionFactory.Create(GetType().Name, ObservabilityKitLogLevel.Debug);
            _gameInteraction.Begin();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            _gameInteraction?.Dispose();
        }

        public override void ViewDidDisappear()
        {
            base.ViewDidDisappear();
            _gameInteraction?.End();
        }
    }
}
