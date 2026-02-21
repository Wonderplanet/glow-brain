using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;

namespace GLOW.Core.Presentation.ViewModels
{
    public record DailyBonusCollectionCellViewModel(
        DailyBonusReceiveStatus DailyBonusReceiveStatus,
        LoginDayCount LoginDayCount,
        PlayerResourceIconViewModel PlayerResourceIconViewModel)
    {
        public static DailyBonusCollectionCellViewModel Empty { get; } = new (
            DailyBonusReceiveStatus.Nothing,
            LoginDayCount.Empty,
            PlayerResourceIconViewModel.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
