using System;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstStageClearTimeRewardDataTranslator
    {
        public static MstStageClearTimeRewardModel Translate(MstStageClearTimeRewardData data)
        {
            return new MstStageClearTimeRewardModel(
                new MasterDataId(data.MstStageId),
                new StageClearTime(TimeSpan.FromMilliseconds(data.UpperClearTimeMs)),
                data.ResourceType,
                new MasterDataId(data.ResourceId),
                new ObscuredPlayerResourceAmount(data.ResourceAmount)
            );
        }
    }
}
