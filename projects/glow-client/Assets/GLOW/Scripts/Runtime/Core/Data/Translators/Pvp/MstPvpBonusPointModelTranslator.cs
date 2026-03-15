using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Data.Translators.Pvp
{
    public class MstPvpBonusPointModelTranslator
    {
        public static MstPvpBonusPointModel ToPvpBonusPointModel(MstPvpBonusPointData data)
        {
            return new MstPvpBonusPointModel(
                new MasterDataId(data.Id),
                data.BonusType,
                new PvpBonusPointConditionValue(data.ConditionValue),
                new PvpBonusPoint(data.BonusPoint));
        }
    }
}