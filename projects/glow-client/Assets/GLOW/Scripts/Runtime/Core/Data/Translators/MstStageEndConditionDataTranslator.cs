using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Data.Translators
{
    public static class MstStateEndConditionDataTranslator
    {
        public static MstStageEndConditionModel ToStageEndConditionModel(MstStageEndConditionData data)
        {
            return new MstStageEndConditionModel(
                new MasterDataId(data.MstStageId),
                data.StageEndType,
                data.ConditionType,
                new BattleEndConditionValue(data.ConditionValue1),
                new BattleEndConditionValue(data.ConditionValue2));
        }
    }
}
