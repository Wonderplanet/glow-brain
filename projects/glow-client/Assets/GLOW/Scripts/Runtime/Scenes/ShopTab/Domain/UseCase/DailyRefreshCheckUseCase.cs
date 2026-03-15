using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.ShopTab.Domain.UseCase
{
    public class DailyRefreshCheckUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }

        public bool IsDailyRefreshTime()
        {
            var lastLoginTime = GameRepository.GetGameFetchOther().UserLoginInfoModel.LastLoginAt;
            if (lastLoginTime == null) return false;

            return DailyResetTimeCalculator.IsPastDailyRefreshTime(lastLoginTime.Value);
        }
    }
}
