using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Data.Translators
{
    public static class OprStepUpGachaDataTranslator
    {
        public static OprStepUpGachaModel Translate(OprStepupGachaData data)
        {
            return new OprStepUpGachaModel(
                OprGachaId: new MasterDataId(data.OprGachaId),
                MaxStepNumber: new StepUpGachaMaxStepNumber(data.MaxStepNumber),
                // MaxLoopCountがnullの場合は無限ループとする
                MaxLoopCount: data.MaxLoopCount.HasValue
                    ? new StepUpGachaMaxLoopCount(data.MaxLoopCount.Value)
                    : StepUpGachaMaxLoopCount.Infinite);
        }
    }
}

