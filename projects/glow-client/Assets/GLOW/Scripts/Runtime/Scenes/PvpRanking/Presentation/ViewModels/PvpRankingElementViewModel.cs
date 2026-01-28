using System.Collections.Generic;
namespace GLOW.Scenes.PvpRanking.Presentation.ViewModels
{
    public record PvpRankingElementViewModel(
        IReadOnlyList<PvpRankingOtherUserViewModel> OtherUserViewModels,
        PvpRankingMyselfUserViewModel MyselfUserViewModel)
    {
        public static PvpRankingElementViewModel Empty { get; } = new (
            new List<PvpRankingOtherUserViewModel>(),
            PvpRankingMyselfUserViewModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
