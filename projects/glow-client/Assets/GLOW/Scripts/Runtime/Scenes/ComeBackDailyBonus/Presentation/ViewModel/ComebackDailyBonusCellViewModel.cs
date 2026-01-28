using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ComeBackDailyBonus.Presentation.ViewModel
{
    public record ComebackDailyBonusCellViewModel(
        DailyBonusReceiveStatus ComebackDailyBonusReceiveStatus,
        LoginDayCount LoginDayCount,
        PlayerResourceIconViewModel PlayerResourceIconViewModel,
        SortOrder SortOrder)
    {
        public static ComebackDailyBonusCellViewModel Empty { get; } = new(
            DailyBonusReceiveStatus.Nothing,
            LoginDayCount.Empty,
            PlayerResourceIconViewModel.Empty,
            SortOrder.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}