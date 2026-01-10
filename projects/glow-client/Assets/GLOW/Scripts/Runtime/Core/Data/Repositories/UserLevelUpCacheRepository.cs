using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Repositories
{
    public class UserLevelUpCacheRepository : IUserLevelUpCacheRepository
    {
        readonly List<UserLevelUpResultModel> _userLevelUpResultHistoryModels = new List<UserLevelUpResultModel>();
        UserLevel _prevUserLevel = UserLevel.Empty;
        UserExp _prevExp = UserExp.Empty;

        void IUserLevelUpCacheRepository.Save(UserLevelUpResultModel resultModel, UserLevel prevUserLevel, UserExp prevExp)
        {
            _userLevelUpResultHistoryModels.Add(resultModel);
            
            if (_prevUserLevel.IsEmpty())
            {
                _prevUserLevel = prevUserLevel;
            }
            
            if(_prevExp.IsEmpty())
            {
                _prevExp = prevExp;
            }
        }

        IReadOnlyList<UserLevelUpResultModel> IUserLevelUpCacheRepository.GetUserLevelUpResultHistoryModels()
        {
            return _userLevelUpResultHistoryModels;
        }

        UserLevel IUserLevelUpCacheRepository.GetPrevUserLevel()
        {
            return _prevUserLevel;
        }

        UserExp IUserLevelUpCacheRepository.GetPrevExp()
        {
            return _prevExp;
        }

        void IUserLevelUpCacheRepository.Clear()
        {
            _userLevelUpResultHistoryModels.Clear();
            _prevUserLevel = UserLevel.Empty;
            _prevExp = UserExp.Empty;
        }
    }
}