using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
namespace GLOW.Scenes.AdventBattleRanking.Presentation.ViewModels
{
    public record AdventBattleRankingElementViewModel(
        IReadOnlyList<AdventBattleRankingOtherUserViewModel> OtherUserViewModels,
        AdventBattleRankingMyselfUserViewModel MyselfUserViewModel,
        AdventBattleName AdventBattleName)
    {
        public static AdventBattleRankingElementViewModel Empty { get; } = new (
            new List<AdventBattleRankingOtherUserViewModel>(),
            AdventBattleRankingMyselfUserViewModel.Empty,
            AdventBattleName.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
