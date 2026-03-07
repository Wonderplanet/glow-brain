using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PartyFormation.Domain.Evaluator
{
    public interface IInGameSpecialRuleUnitStatusEvaluator
    {
        InGameSpecialRuleUnitStatusTargetFlag EvaluateTarget(
            UserUnitModel userUnit,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels);

        InGameSpecialRuleUnitStatusTargetFlag EvaluateTarget(
            MstCharacterModel mstCharacter,
            MstInGameSpecialRuleUnitStatusModel specialRuleUnitStatusModel);

        InGameSpecialRuleUnitStatusTargetFlag EvaluateTarget(
            MstCharacterModel mstCharacter,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels);

        InGameSpecialRuleUnitStatusFlag HasSpecialRuleUnitStatus(
            MasterDataId contentId,
            InGameContentType inGameContentType);
    }
}
