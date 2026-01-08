using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.PvpTop.Domain.Model;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Data.Repositories
{
    public class PvpTopCacheRepository : IPvpTopCacheRepository
    {
        ObscuredDateTimeOffset _matchUserRefreshedTime = DateTimeOffset.MinValue;
        PvpTopApiCallAllowedStatus _isPvpTopApiCallAllowed = PvpTopApiCallAllowedStatus.Empty;
        PvpTopResultModel _cachedPvpTopResultModel = PvpTopResultModel.Empty;

        DateTimeOffset IPvpTopCacheRepository.GetOpponentRefreshedTime()
        {
            return _matchUserRefreshedTime;
        }

        void IPvpTopCacheRepository.SetOpponentRefreshedTime(DateTimeOffset refreshTime)
        {
            _matchUserRefreshedTime = refreshTime;
        }

        PvpTopApiCallAllowedStatus IPvpTopCacheRepository.GetPvpTopApiCallAllowedStatus()
        {
            return _isPvpTopApiCallAllowed;
        }

        void IPvpTopCacheRepository.SetPvpTopApiCallAllowedStatus(PvpTopApiCallAllowedStatus isPvpTopApiCallAllowed)
        {
            _isPvpTopApiCallAllowed = isPvpTopApiCallAllowed;
        }

        PvpTopResultModel IPvpTopCacheRepository.GetCachedPvpTopResultModel()
        {
            return _cachedPvpTopResultModel;
        }

        void IPvpTopCacheRepository.SetCachedPvpTopResultModel(PvpTopResultModel pvpTopResultModel)
        {
            _cachedPvpTopResultModel = pvpTopResultModel;
        }

        void IPvpTopCacheRepository.SetCachedPvpTopResultModelAtOpponents(
            IReadOnlyList<OpponentSelectStatusModel> pvpOpponentModels)
        {
            if (_cachedPvpTopResultModel.IsEmpty())
            {
                _cachedPvpTopResultModel = PvpTopResultModel.Empty;
            }

            _cachedPvpTopResultModel = _cachedPvpTopResultModel with
            {
                OpponentSelectStatuses = pvpOpponentModels
            };
        }
    }
}
