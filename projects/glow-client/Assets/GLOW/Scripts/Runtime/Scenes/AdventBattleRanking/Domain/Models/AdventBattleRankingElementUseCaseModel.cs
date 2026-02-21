using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
namespace GLOW.Scenes.AdventBattleRanking.Domain.Models
{
    public record AdventBattleRankingElementUseCaseModel(
        IReadOnlyList<AdventBattleRankingOtherUserUseCaseModel> OtherUserModels,
        AdventBattleRankingMyselfUserUseCaseModel MyselfUserModel,
        AdventBattleName AdventBattleName)
    {
        public static AdventBattleRankingElementUseCaseModel Empty { get; } = new (
            new List<AdventBattleRankingOtherUserUseCaseModel>(),
            AdventBattleRankingMyselfUserUseCaseModel.Empty,
            AdventBattleName.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
