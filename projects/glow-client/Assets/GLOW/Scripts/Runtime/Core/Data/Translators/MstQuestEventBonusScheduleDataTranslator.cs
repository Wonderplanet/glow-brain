using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstQuestEventBonusScheduleDataTranslator
    {
        public static MstQuestEventBonusScheduleModel Translate(MstQuestEventBonusScheduleData data)
        {
            return new MstQuestEventBonusScheduleModel(
                new MasterDataId(data.MstQuestId),
                new EventBonusGroupId(data.EventBonusGroupId),
                data.StartAt,
                data.EndAt
                );
        }
    }
}
