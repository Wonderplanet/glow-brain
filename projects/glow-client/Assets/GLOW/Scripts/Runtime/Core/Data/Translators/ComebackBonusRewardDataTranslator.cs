using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.ComebackDailyBonus;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Data.Translators
{
    public static class ComebackBonusRewardDataTranslator
    {
        public static ComebackBonusRewardModel ToComebackBonusRewardModel(ComebackBonusRewardData data)
        {
            return new ComebackBonusRewardModel(
                new MasterDataId(data.MstComebackBonusScheduleId),
                new LoginDayCount(data.LoginDayCount),
                RewardDataTranslator.Translate(data.Reward));
        }
    }
}