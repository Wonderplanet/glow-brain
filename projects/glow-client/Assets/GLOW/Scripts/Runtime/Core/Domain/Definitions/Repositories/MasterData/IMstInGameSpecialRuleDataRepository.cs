using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstInGameSpecialRuleDataRepository
    {
        IReadOnlyList<MstInGameSpecialRuleModel> GetInGameSpecialRuleModels(
            MasterDataId specialRuleTargetMstId,
            InGameContentType specialRuleContentType);
    }
}
