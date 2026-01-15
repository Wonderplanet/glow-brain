using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.HomePartyFormation.Domain.Evaluators
{
    public interface IRecommendPartyFormationEvaluator
    {
        List<UserUnitModel> GetRecommendPartyFormationUnits(
            EventBonusGroupId eventBonusGroupId,
            MasterDataId mstSpecialRuleTargetId,
            InGameContentType contentType,
            MasterDataId enhanceQuestId,
            PartyMemberSlotCount partyMemberSlotCount);
    }
}
