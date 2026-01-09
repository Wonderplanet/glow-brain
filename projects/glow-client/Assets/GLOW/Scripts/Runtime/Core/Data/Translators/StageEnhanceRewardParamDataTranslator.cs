using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Data.Translators
{
    public class StageEnhanceRewardParamDataTranslator
    {
        public static MstStageEnhanceRewardParamModel ToStageEnhanceRewardParamModel(
            MstStageEnhanceRewardParamData data)
        {
            return new MstStageEnhanceRewardParamModel(
                new MasterDataId(data.Id),
                new EnhanceQuestMinThresholdScore(data.MinThresholdScore),
                new ItemAmount(data.CoinRewardAmount),
                data.CoinRewardSizeType);
        }
    }
}
