using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Modules.Tutorial.Domain.UseCases;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction
{
    /// <summary>
    /// チュートリアル
    /// </summary>
    public class TutorialAction : IHomeAppearanceAction
    {
        public class Factory : PlaceholderFactory<TutorialAction> { }

        [Inject] ITutorialContext TutorialContext { get; }
        [Inject] CheckTutorialCompletedUseCase CheckTutorialCompletedUseCase { get; }

        public async UniTask ExecuteAsync(HomeAppearanceActionContext context, Action onCloseCompletion,
            CancellationToken cancellationToken)
        {
            // 初回の強制チュートリアル開始チェック
            await TutorialContext.DoIfTutorial(() => UniTask.CompletedTask);

            // チュートリアル中の場合は後続の処理をスキップするためにOperationCanceledExceptionを投げる
            if(!CheckTutorialCompletedUseCase.CheckTutorialCompleted())
            {
                throw new OperationCanceledException(cancellationToken);
            }
        }
    }
}
