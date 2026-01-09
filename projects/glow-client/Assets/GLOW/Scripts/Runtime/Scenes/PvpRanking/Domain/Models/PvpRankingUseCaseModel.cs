namespace GLOW.Scenes.PvpRanking.Domain.Models
{
    public record PvpRankingUseCaseModel(
        PvpRankingElementUseCaseModel CurrentRanking,
        PvpRankingElementUseCaseModel PreviousRanking)
    {
        public static PvpRankingUseCaseModel Empty { get; } = new (
            PvpRankingElementUseCaseModel.Empty,
            PvpRankingElementUseCaseModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
