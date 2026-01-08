namespace GLOW.Scenes.ShopBuyConform.Presentation.View
{
    public interface ICashBuyConfirmViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnSpecificCommerceSelected();
        void OnFundsSettlementSelected();
        void OnBuySelected();
        void OnCloseSelected();
    }
}