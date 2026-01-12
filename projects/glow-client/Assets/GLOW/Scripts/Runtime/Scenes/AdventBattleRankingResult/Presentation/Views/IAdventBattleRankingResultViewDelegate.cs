namespace GLOW.Scenes.AdventBattleRankingResult.Presentation.Views
{
    public interface IAdventBattleRankingResultViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidAppear();
        void OnCloseButtonTapped();
        void OnEscape();
    }
}
