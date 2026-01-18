using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstStageEventSettingDataRepository
    {
        IReadOnlyList<MstStageEventSettingModel> GetStageEventSettings();
        MstStageEventSettingModel GetStageEventSettingFirstOrDefault(MasterDataId mstStageId);
    }
}
