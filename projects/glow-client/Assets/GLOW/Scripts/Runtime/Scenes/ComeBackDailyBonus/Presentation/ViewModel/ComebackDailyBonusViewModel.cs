using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;

namespace GLOW.Scenes.ComeBackDailyBonus.Presentation.ViewModel
{
    public record ComebackDailyBonusViewModel(
        LoginDayCount LoginDayCount,
        IReadOnlyList<DailyBonusCollectionCellViewModel>  ComebackDailyBonusCellModels,
        IReadOnlyList<CommonReceiveResourceViewModel> CommonReceiveResourceModels,
        RemainingTimeSpan RemainingTime)
    {
        public static ComebackDailyBonusViewModel Empty { get; } = new(
            LoginDayCount.Empty,
            new List<DailyBonusCollectionCellViewModel>(),
            new List<CommonReceiveResourceViewModel>(),
            RemainingTimeSpan.Empty);
        
        public DailyBonusCollectionCellViewModel GetReceivingAnimationPlayDayCell()
        {
            var receivingReward = ComebackDailyBonusCellModels.FirstOrDefault(
                cell => cell.DailyBonusReceiveStatus == DailyBonusReceiveStatus.Receiving,
                DailyBonusCollectionCellViewModel.Empty);
            return receivingReward;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}