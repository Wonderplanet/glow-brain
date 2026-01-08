namespace GLOW.Scenes.PvpRanking.Presentation.Views
{
    public interface IPvpRankingViewDelegate
    {
        void OnViewDidLoad();
        void OnHelpButtonTapped();
        void OnBackButtonTapped();
        void OnCurrentRankingButtonTapped();
        void OnPrevRankingButtonTapped();
    }
}
