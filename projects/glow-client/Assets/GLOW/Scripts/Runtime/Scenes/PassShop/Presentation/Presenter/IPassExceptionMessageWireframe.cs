using System;

namespace GLOW.Scenes.PassShop.Presentation.Presenter
{
    public interface IPassExceptionMessageWireframe
    {
        void ShowExpiredPassPurchaseErrorMessage(Action onClose);
        void ShowAlreadyPurchasedPassMessage(Action onClose);
    }
}