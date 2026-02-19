namespace GLOW.Scenes.PassShopBuyConfirm.Presentation.View
{
    public interface IPassShopBuyConfirmViewDelegate
    {
        void OnViewDidLoad();
        void OnCloseSelected();
        void OnBuySelected();
        void ShowSpecificCommerce();
        void ShowFundsSettlement();
    }
}