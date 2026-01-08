using System;

namespace GLOW.Scenes.MessageBox.Presentation.Presenter
{
    public interface IUnreceivedMessageWireframe
    {
        void ShowUnreceivedExpiredMessageView(Action onClose);
        void ShowUnopenedExpiredMessageView(Action onClose);
        void ShowUnreceivedLimitAmountMessageView(Action onClose);
    }
}