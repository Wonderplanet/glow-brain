using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PvpTop.Domain.Model;
using GLOW.Scenes.PvpTop.Domain.ModelFactories;
using GLOW.Scenes.PvpTop.Domain.Resolver;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.PvpTop.Domain.UseCase
{
    public class PvpTopOpponentUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPvpTopCacheRepository PvpTopCacheRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IPvpService PvpService { get; }
        [Inject] IPvpTopOpponentModelFactory PvpTopOpponentModelFactory { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IMstCurrentPvpModelResolver MstCurrentPvpModelResolver { get; }

        public async UniTask<PvpTopOpponentUseCaseModel> RefreshMatchUser(CancellationToken ct)
        {
            var model = await PvpService.ChangeOpponent(ct);
            UpdateRepositories(model.OpponentSelectStatuses);

            var coolTimeSeconds =MstConfigRepository
                .GetConfig(MstConfigKey.PvpOpponentRefreshCoolTimeSeconds).Value
                .ToInt();
            var coolTime = new PvpOpponentRefreshCoolTime(coolTimeSeconds);

            // マスターデータからPVP情報を取得
            var sysPvpSeasonModel = GameRepository.GetGameFetchOther().SysPvpSeasonModel;

            var mstPvpModel = MstCurrentPvpModelResolver.CreateMstPvpModel(sysPvpSeasonModel.Id);

            return new PvpTopOpponentUseCaseModel(
                coolTime,
                PvpTopOpponentModelFactory.Create(model.OpponentSelectStatuses, mstPvpModel.Id)
                );
        }

        // 副作用
        void UpdateRepositories(IReadOnlyList<OpponentSelectStatusModel> opponentModels)
        {
            PvpTopCacheRepository.SetOpponentRefreshedTime(TimeProvider.Now);
            PvpTopCacheRepository.SetCachedPvpTopResultModelAtOpponents(opponentModels);
        }
    }
}
