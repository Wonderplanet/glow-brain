using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Data.Translators
{
    public static class OprStepUpGachaStepDataTranslator
    {
        public static OprStepUpGachaStepModel Translate(
            OprStepupGachaStepData data, 
            OprStepupGachaStepI18nData i18nData)
        {
            return new OprStepUpGachaStepModel(
                OprGachaId: new MasterDataId(data.OprGachaId),
                StepNumber: new StepUpGachaStepNumber(data.StepNumber),
                CostType: data.CostType,
                MstCostId: new MasterDataId(data.CostId),
                CostAmount: new CostAmount(data.CostNum),
                DrawCount: new GachaDrawCount(data.DrawCount),
                FixedPrizeDescription: string.IsNullOrEmpty(i18nData.FixedPrizeDescription) 
                    ? GachaFixedPrizeDescription.Empty 
                    : new GachaFixedPrizeDescription(i18nData.FixedPrizeDescription),
                IsFirstFree: data.IsFirstFree ? IsFirstFreeFlag.True : IsFirstFreeFlag.False);
        }
    }
}

