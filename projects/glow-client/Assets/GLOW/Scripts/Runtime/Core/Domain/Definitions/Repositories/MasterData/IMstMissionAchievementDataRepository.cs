using System.Collections.Generic;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstMissionAchievementDataRepository
    {
        public IReadOnlyList<MstMissionAchievementModel> GetMstMissionAchievementModels();
        public IReadOnlyList<MstMissionAchievementDependencyModel> GetMstMissionAchievementDependencyModels();
    }
}