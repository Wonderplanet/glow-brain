using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Data.Translators
{
    public static class OprStepUpGachaStepRewardDataTranslator
    {
        public static OprStepUpGachaStepRewardModel Translate(OprStepupGachaStepRewardData data)
        {
            return new OprStepUpGachaStepRewardModel(
                OprGachaId: new MasterDataId(data.OprGachaId),
                StepNumber: new StepUpGachaStepNumber(data.StepNumber),
                // LoopCountTargetがnullの場合は全てのループを対象とする
                LoopCountTarget: data.LoopCountTarget.HasValue
                    ? new StepUpGachaLoopCountTarget(data.LoopCountTarget.Value)
                    : StepUpGachaLoopCountTarget.All,
                ResourceType: data.ResourceType,
                ResourceId: string.IsNullOrEmpty(data.ResourceId) 
                    ? MasterDataId.Empty 
                    : new MasterDataId(data.ResourceId),
                ResourceAmount: new ItemAmount(data.ResourceAmount));
        }
    }
}

