using System;

namespace GLOW.Scenes.Notice.Presentation.View
{
    public interface INoticeDialogViewDelegate
    {
        void OnViewWillAppear();
        void OnCloseSelected();
        void OnTransitSelected();
    }
}