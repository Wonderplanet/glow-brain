using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.ComebackDailyBonus;

namespace GLOW.Core.Data.Translators
{
    public static class MstComebackBonusScheduleDataTranslator
    {
        public static MstComebackBonusScheduleModel ToMstComebackBonusModel(MstComebackBonusScheduleData data)
        {
            return new MstComebackBonusScheduleModel(
                new MasterDataId(data.Id),
                new InactiveConditionDays(data.InactiveConditionDays),
                new ComebackDailyBonusDurationDays(data.DurationDays),
                new ComebackDailyBonusStartAt(data.StartAt),
                new ComebackDailyBonusEndAt(data.EndAt));
        }
    }
}