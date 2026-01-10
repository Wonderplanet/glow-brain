using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.PvpTop.Domain.UseCase
{
    public class PvpOpponentRefreshCoolTimeFactory : IPvpOpponentRefreshCoolTimeFactory
    {
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IPvpTopCacheRepository PvpTopCacheRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }

        public PvpOpponentRefreshCoolTime CalculateRefreshCoolTime()
        {
            var refreshedTime = PvpTopCacheRepository
                .GetOpponentRefreshedTime();

            // CacheRespositoryが初期値だったら0を返す
            if (refreshedTime == DateTimeOffset.MinValue)
            {
                return new PvpOpponentRefreshCoolTime(0);
            }

            var coolTimeSecondValue =MstConfigRepository
                .GetConfig(MstConfigKey.PvpOpponentRefreshCoolTimeSeconds).Value
                .ToInt();

            var availableTime = refreshedTime
                .AddSeconds(coolTimeSecondValue);

            var coolTimeSeconds = (int)(availableTime - TimeProvider.Now).TotalSeconds;
            if (coolTimeSeconds < 0)
            {
                coolTimeSeconds = 0; // クールタイムがマイナスにならないようにする
            }

            return new PvpOpponentRefreshCoolTime(coolTimeSeconds);
        }
    }
}
