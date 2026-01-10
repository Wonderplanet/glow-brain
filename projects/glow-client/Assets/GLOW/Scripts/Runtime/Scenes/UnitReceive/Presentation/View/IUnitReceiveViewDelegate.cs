using System;

namespace GLOW.Scenes.UnitReceive.Presentation.View
{
    public interface IUnitReceiveViewDelegate
    {
        void OnViewWillAppear();
        void OnCloseButtonTapped(Action onCloseCompletion);
    }
}
