using System.Collections.Generic;
namespace GLOW.Scenes.PvpRanking.Domain.Models
{
    public record PvpRankingElementUseCaseModel(
        IReadOnlyList<PvpRankingOtherUserUseCaseModel> OtherUserModels,
        PvpRankingMyselfUserUseCaseModel MyselfUserModel)
    {
        public static PvpRankingElementUseCaseModel Empty { get; } = new (
            new List<PvpRankingOtherUserUseCaseModel>(),
            PvpRankingMyselfUserUseCaseModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
