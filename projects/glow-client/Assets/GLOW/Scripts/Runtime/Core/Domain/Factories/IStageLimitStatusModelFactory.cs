using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.Models;

namespace GLOW.Core.Domain.Factories
{
    public interface IStageLimitStatusModelFactory
    {
        InGameSpecialRuleStatusModel CreateInvalidStageLimitStatusModel(
            MasterDataId specialRuleTargetMstId,
            InGameContentType specialRuleContentType,
            PartyName partyName,
            IReadOnlyList<MstCharacterModel> characterModels);
        InGameSpecialRuleStatusModel CreateStageLimitStatusModel(
            MasterDataId specialRuleTargetMstId,
            InGameContentType specialRuleContentType,
            PartyName partyName);
    }
}
