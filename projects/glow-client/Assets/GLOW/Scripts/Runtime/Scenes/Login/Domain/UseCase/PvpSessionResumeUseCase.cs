using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.AdventBattleRanking.Domain.ModelFactories;
using GLOW.Scenes.PvpTop.Domain.Model;
using Zenject;

namespace GLOW.Scenes.Login.Domain.UseCase
{
    public class PvpSessionResumeUseCase
    {
        [Inject] IPvpService PvpService { get; }
        [Inject] IPvpStartModelFactory PvpStartModelFactory { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }
        [Inject] IResumableStateRepository ResumableStateRepository { get; }
        [Inject] ISelectedStageRepository SelectedStageRepository { get; }

        public async UniTask ResumePvp(CancellationToken cancellationToken)
        {
            var resumeModel =  await PvpService.Resume(cancellationToken);

            var startUseCaseModel = PvpStartModelFactory.CreatePvpStartUseCaseModel(resumeModel);
            var sysPvpSeasonModel = GameRepository.GetGameFetchOther().SysPvpSeasonModel;
            SetRepositories(sysPvpSeasonModel, startUseCaseModel);
        }

        // 副作用
        void SetRepositories(
            SysPvpSeasonModel sysPvpSeasonModel,
            PvpStartUseCaseModel pvpStartUseCaseModel)
        {
            SelectedStageRepository.Save(
                new SelectedStageModel(MasterDataId.Empty, MasterDataId.Empty, sysPvpSeasonModel.Id));
            ResumableStateRepository.Save(
                new ResumableStateModel(SceneViewContentCategory.Pvp, sysPvpSeasonModel.Id.ToMasterDataId(), MasterDataId.Empty));
            PvpSelectedOpponentStatusCacheRepository.SetOpponentStatus(pvpStartUseCaseModel.OpponentPvpStatus);
        }
    }
}
