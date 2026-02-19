namespace GLOW.Scenes.PvpRanking.Presentation.ViewModels
{
    public record PvpRankingViewModel(
        PvpRankingElementViewModel CurrentRanking,
        PvpRankingElementViewModel PrevRanking)
    {
        public static PvpRankingViewModel Empty { get; } = new (
            PvpRankingElementViewModel.Empty,
            PvpRankingElementViewModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
