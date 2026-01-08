using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstInGameSpecialRuleUnitStatusDataRepository
    {
        IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> GetInGameSpecialRuleUnitStatusModels(
            MasterDataId groupId);

        IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> GetInGameSpecialRuleUnitStatusModels(
            IReadOnlyList<MasterDataId> groupIdList);
    }
}
