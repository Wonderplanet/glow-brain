using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Modules.CommonReceiveView.Domain.Model;

namespace GLOW.Scenes.ComeBackDailyBonus.Domain.Model
{
    public record ComebackDailyBonusModel(
        LoginDayCount LoginDayCount,
        IReadOnlyList<ComebackDailyBonusCellModel> ComebackDailyBonusCellModels,
        IReadOnlyList<CommonReceiveResourceModel> CommonReceiveResourceModels,
        RemainingTimeSpan RemainingTime)
    {
        public static ComebackDailyBonusModel Empty { get; } = new(
            LoginDayCount.Empty,
            new List<ComebackDailyBonusCellModel>(),
            new List<CommonReceiveResourceModel>(),
            RemainingTimeSpan.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}