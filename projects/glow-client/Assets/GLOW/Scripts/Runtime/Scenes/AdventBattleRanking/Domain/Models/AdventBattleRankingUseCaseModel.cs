namespace GLOW.Scenes.AdventBattleRanking.Domain.Models
{
    public record AdventBattleRankingUseCaseModel(AdventBattleRankingElementUseCaseModel CurrentRanking)
    {
        public static AdventBattleRankingUseCaseModel Empty { get; } = new (AdventBattleRankingElementUseCaseModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
