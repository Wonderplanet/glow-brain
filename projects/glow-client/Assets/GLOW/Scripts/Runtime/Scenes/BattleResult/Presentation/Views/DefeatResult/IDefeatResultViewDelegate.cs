namespace GLOW.Scenes.BattleResult.Presentation.Views.DefeatResult
{
    public interface IDefeatResultViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnCloseSelected();
        void OnRetrySelected();
    }
}
