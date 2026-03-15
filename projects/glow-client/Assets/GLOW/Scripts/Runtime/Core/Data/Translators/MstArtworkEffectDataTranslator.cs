using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.ArtworkEffect;

namespace GLOW.Core.Data.Translators
{
    public class MstArtworkEffectDataTranslator
    {
        public static MstArtworkEffectModel TranslateEffect(
            MstArtworkEffectData effectData,
            IReadOnlyList<MstArtworkEffectTargetRuleData> targetRuleDataList,
            IReadOnlyList<MstArtworkEffectActivationRuleData> activationRuleDataList)
        {
            var targetRules = targetRuleDataList
                .Select(
                    targetRule => new MstArtworkEffectTargetRuleModel(
                        targetRule.ConditionType,
                        new ArtworkEffectTargetValue(targetRule.ConditionValue)))
                .ToList();

            var activationRules = activationRuleDataList
                .Select(
                    activationRule => new MstArtworkEffectActivationRuleModel(
                        activationRule.ConditionType,
                        new ArtworkEffectActivationValue(activationRule.ConditionValue)))
                .ToList();

            return new MstArtworkEffectModel(
                effectData.EffectType,
                new ArtworkEffectValue((decimal)effectData.GradeLevel1Value),
                new ArtworkEffectValue((decimal)effectData.GradeLevel2Value),
                new ArtworkEffectValue((decimal)effectData.GradeLevel3Value),
                new ArtworkEffectValue((decimal)effectData.GradeLevel4Value),
                new ArtworkEffectValue((decimal)effectData.GradeLevel5Value),
                targetRules,
                activationRules);
        }

        public static MstInGameArtworkEffectModel TranslateInGameModel(
            MasterDataId artworkId,
            IReadOnlyList<MstArtworkEffectModel> artworkEffectModels)
        {
            return new MstInGameArtworkEffectModel(artworkId, artworkEffectModels);
        }
    }
}
