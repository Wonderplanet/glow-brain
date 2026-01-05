using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;

namespace GLOW.Core.Data.Repositories
{
    public class MissionOfOfAdventBattleRepository : IMissionOfAdventBattleRepository
    {
        IReadOnlyList<UserMissionEventModel> _userMissionEventModels;
        IReadOnlyList<UserMissionLimitedTermModel> _userMissionLimitedTermModels;

        void IMissionOfAdventBattleRepository.SetUserMissionEventModels(IReadOnlyList<UserMissionEventModel> userMissionEventModels)
        {
            _userMissionEventModels = userMissionEventModels;
        }

        void IMissionOfAdventBattleRepository.SetUserMissionLimitedTermModels(IReadOnlyList<UserMissionLimitedTermModel> userMissionLimitedTermModels)
        {
            _userMissionLimitedTermModels = userMissionLimitedTermModels;
        }

        public IReadOnlyList<UserMissionEventModel> GetUserMissionEventModels()
        {
            return _userMissionEventModels;
        }

        public IReadOnlyList<UserMissionLimitedTermModel> GetUserMissionLimitedTermModels()
        {
            return _userMissionLimitedTermModels;
        }
        
    }
}