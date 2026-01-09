using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IUserLevelUpCacheRepository
    {
        void Save(
            UserLevelUpResultModel resultModel, 
            UserLevel prevUserLevel, 
            UserExp prevExp);
        IReadOnlyList<UserLevelUpResultModel> GetUserLevelUpResultHistoryModels();
        UserLevel GetPrevUserLevel();
        UserExp GetPrevExp();
        void Clear();
    }
}