namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    public interface IContinueActionSelectionViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnCancelSelected();
        void OnContinueDiamondSelected();
        void OnContinueAdSelected();
    }
}
