namespace GLOW.Scenes.ShopBuyConform.Presentation.View
{
    public interface IExchangeConfirmViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnExchangeSelected();
        void OnCancelSelected();
    }
}
