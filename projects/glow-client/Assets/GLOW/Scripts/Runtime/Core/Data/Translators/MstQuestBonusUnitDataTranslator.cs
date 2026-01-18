using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstQuestBonusUnitDataTranslator
    {
        public static MstQuestBonusUnitModel Translate(MstQuestBonusUnitData data)
        {
            return new MstQuestBonusUnitModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstQuestId),
                new MasterDataId(data.MstUnitId),
                new BonusRate(data.CoinBonusRate),
                data.StartAt,
                data.EndAt
            );
        }
    }
}
