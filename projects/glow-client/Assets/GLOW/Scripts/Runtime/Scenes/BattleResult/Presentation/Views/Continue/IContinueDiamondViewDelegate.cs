namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    public interface IContinueDiamondViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnCancelSelected();
        void OnSpecificCommerceSelected();
        void OnContinueDiamondSelected();
        void OnPurchaseSelected();
    }
}
