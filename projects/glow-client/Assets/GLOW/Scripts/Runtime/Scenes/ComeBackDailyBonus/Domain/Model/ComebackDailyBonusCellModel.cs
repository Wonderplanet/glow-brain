using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.ComeBackDailyBonus.Domain.Model
{
    public record ComebackDailyBonusCellModel(
        DailyBonusReceiveStatus ComebackDailyBonusReceiveStatus,
        LoginDayCount LoginDayCount,
        PlayerResourceModel PlayerResourceModel)
    {
        public static ComebackDailyBonusCellModel Empty { get; } = new(
            DailyBonusReceiveStatus.Nothing,
            LoginDayCount.Empty,
            PlayerResourceModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}