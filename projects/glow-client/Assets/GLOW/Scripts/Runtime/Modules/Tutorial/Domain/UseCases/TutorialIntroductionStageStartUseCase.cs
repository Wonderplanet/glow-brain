using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.Definitions;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class TutorialIntroductionStageStartUseCase
    {
        [Inject] IResumableStateRepository ResumableStateRepository { get; }
        [Inject] ITutorialService TutorialService { get; }
        [Inject] IGameRepository GameRepository { get; }

        public async UniTask StartStage(CancellationToken cancellationToken, MasterDataId mstStageId)
        {
            ResumableStateRepository.Save(new ResumableStateModel(SceneViewContentCategory.MainStage, mstStageId, MasterDataId.Empty));

            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            await TutorialService.StartTutorialStage(cancellationToken, tutorialStatus.TutorialFunctionName, PartyNo.One);
        }
    }
}
