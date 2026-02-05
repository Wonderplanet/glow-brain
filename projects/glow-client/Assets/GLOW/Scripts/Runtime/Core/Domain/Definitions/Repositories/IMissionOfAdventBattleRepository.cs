using System.Collections.Generic;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMissionOfAdventBattleRepository
    {
        void SetUserMissionEventModels(IReadOnlyList<UserMissionEventModel> userMissionEventModels);
        void SetUserMissionLimitedTermModels(IReadOnlyList<UserMissionLimitedTermModel> userMissionLimitedTermModels);
        IReadOnlyList<UserMissionEventModel> GetUserMissionEventModels();
        IReadOnlyList<UserMissionLimitedTermModel> GetUserMissionLimitedTermModels();
    }
}