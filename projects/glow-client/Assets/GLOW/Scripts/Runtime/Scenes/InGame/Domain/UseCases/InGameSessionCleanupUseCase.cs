using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.UseCases;
using GLOW.Scenes.Login.Domain.UseCase;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class InGameSessionCleanupUseCase
    {
        [Inject] IResumableStateRepository ResumableStateRepository { get; }
        [Inject] SessionCleanupUseCase SessionCleanupUseCase { get; }

        public async UniTask CleanupSession(CancellationToken cancellationToken)
        {
            var resumableStateModel = ResumableStateRepository.Get();
            if (!resumableStateModel.IsEmpty())
            {
                InGameContentType inGameContentType = resumableStateModel.Category switch
                {
                    SceneViewContentCategory.AdventBattle => InGameContentType.AdventBattle,
                    SceneViewContentCategory.EnhanceStage => InGameContentType.Stage,
                    SceneViewContentCategory.Pvp => InGameContentType.Pvp,
                    _ => throw new ArgumentOutOfRangeException(
                        nameof(resumableStateModel.Category),
                        resumableStateModel.Category,
                        "Unsupported SceneViewContentCategory"),
                };

                await SessionCleanupUseCase.CleanupSession(cancellationToken, inGameContentType);
            }
        }
    }
}
