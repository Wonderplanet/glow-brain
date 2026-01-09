using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PvpTop.Domain.ModelFactories;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.PvpTop.Domain.UseCase
{
    public class PvpTopUseCase
    {
        [Inject] IPvpTopModelFactory PvpTopModelFactory { get; }
        [Inject] IPvpService PvpService { get; }
        [Inject] IPvpTopCacheRepository PvpTopCacheRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }

        public async UniTask<PvpTopUseCaseModel> UpdateAndGetModel(CancellationToken cancellationToken)
        {
            if (CanCallApiBasedOnCacheTime())
            {
                var resultModel = await PvpService.Top(cancellationToken);
                SetRepositories(resultModel);
                return PvpTopModelFactory.Create(resultModel);
            }
            else
            {
                // キャッシュから結果モデルを取得
                var cachedResultModel = PvpTopCacheRepository.GetCachedPvpTopResultModel();
                return PvpTopModelFactory.Create(cachedResultModel);
            }
        }

        bool CanCallApiBasedOnCacheTime()
        {
            var status = PvpTopCacheRepository.GetPvpTopApiCallAllowedStatus();
            if (status.IsApiCallAllowed()) return true;

            var coolTimeMinute =
                MstConfigRepository.GetConfig(MstConfigKey.PvpTopApiRequestCoolTimeMinute).Value;

            return status.UpdatedAt.AddMinutes(coolTimeMinute.ToInt()) <= TimeProvider.Now;
        }

        // 副作用
        void SetRepositories(PvpTopResultModel model)
        {
            // 2回目以降は前シーズン結果を出さないために空にする
            var cachedModel = model with
            {
                PvpPreviousSeasonResult = PvpPreviousSeasonResultModel.Empty
            };

            PvpTopCacheRepository
                .SetPvpTopApiCallAllowedStatus(new PvpTopApiCallAllowedStatus(false, TimeProvider.Now));
            PvpTopCacheRepository.SetCachedPvpTopResultModel(cachedModel);

            var gameFetchOther = GameRepository.GetGameFetchOther();
            var updatedGameFetchOther = gameFetchOther with
            {
                UserPvpStatusModel = cachedModel.UsrPvpStatus
            };

            GameManagement.SaveGameFetchOther(updatedGameFetchOther);

            // ランクマッチの通知を更新する
            LocalNotificationScheduler.RefreshRemainPvPSchedule();
        }
    }
}
