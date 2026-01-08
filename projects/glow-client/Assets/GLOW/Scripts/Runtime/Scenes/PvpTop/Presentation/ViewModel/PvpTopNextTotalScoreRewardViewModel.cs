using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.PvpTop.Presentation.ViewModel
{
    public record PvpTopNextTotalScoreRewardViewModel(
        PlayerResourceIconViewModel NextTotalScoreReward,
        PvpPoint NextTotalScore)
    {
        public static PvpTopNextTotalScoreRewardViewModel Empty { get; } = new(
            PlayerResourceIconViewModel.Empty,
            PvpPoint.Zero);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}