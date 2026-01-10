using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Event;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstEventDataRepository
    {
        MstEventModel GetEvent(MasterDataId id);
        MstEventModel GetEventFirstOrDefault(MasterDataId mstEventId);
        IReadOnlyList<MstEventModel> GetEvents();
        IReadOnlyList<MstEventDisplayRewardModel> GetEventDisplayRewards();

    }
}
