namespace GLOW.Scenes.ShopBuyConform.Presentation.View
{
    public interface ICoinBuyConfirmViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnTradeSelected();
        void OnCloseSelected();
    }
}