using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.ComebackDailyBonus;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Data.Translators
{
    public static class UserComebackBonusProgressDataTranslator
    {
        public static UserComebackBonusProgressModel ToUserComebackBonusProgressModel(UsrComebackBonusProgressData data)
        {
            return new UserComebackBonusProgressModel(
                new MasterDataId(data.MstComebackBonusScheduleId),
                new LoginDayCount(data.Progress),
                new ComebackDailyBonusStartAt(data.StartAt),
                new ComebackDailyBonusEndAt(data.EndAt)
            );
        }
    }
}