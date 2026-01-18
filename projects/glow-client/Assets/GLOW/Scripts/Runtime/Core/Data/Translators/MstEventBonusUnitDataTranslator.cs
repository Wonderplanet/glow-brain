using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstEventBonusUnitDataTranslator
    {
        public static MstEventBonusUnitModel Translate(MstEventBonusUnitData data)
        {
            return new MstEventBonusUnitModel(
                new MasterDataId(data.MstUnitId),
                new EventBonusPercentage(data.BonusPercentage),
                new EventBonusGroupId(data.EventBonusGroupId),
                new PickUpFlag(data.IsPickUp)
                );
        }
    }
}
