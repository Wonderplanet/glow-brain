using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Domain.Evaluator;
using GLOW.Modules.Tutorial.Presentation.Sequence.FreePart;
using Zenject;

namespace GLOW.Modules.Tutorial.Application.Context
{
    // TODO: ホームで原画の獲得演出を使うようになったので、「InGame」の単語を外したい
    public class InGameResultFreePartTutorialContext : IInGameResultFreePartTutorialContext, IDisposable
    {
        [Inject] PlaceholderFactory<ArtworkFragmentTutorialSequence> ArtworkFragmentTutorialSequenceFactory { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }

        CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();
        bool _isPlayingTutorialSequence;

        public async UniTask DoIfTutorial(Func<UniTask> action)
        {
            await PlaySequenceIfNeeds();

            await action();
        }

        public void DoIfTutorial(Action action)
        {
            if (_isPlayingTutorialSequence) return;

            action.Invoke();
        }

        async UniTask PlaySequenceIfNeeds()
        {
            if (_isPlayingTutorialSequence) return;

            var gameFetchOther = GameRepository.GetGameFetchOther();

            // メインパートチュートリアルが完了していない場合はスキップ
            if (!gameFetchOther.TutorialStatus.IsCompleted()) return;

            _isPlayingTutorialSequence = true;

            // 達成済みのフリーパートを取得
            var freeParts = gameFetchOther.UserTutorialFreePartModels;

            if (TutorialEvaluator.CanBeginTutorial(
                    MstTutorialRepository,
                    GameRepository,
                    freeParts,
                    TutorialFreePartIdDefinitions.ArtworkFragment))
            {
                // 原画のかけら獲得チュートリアル 条件はないが開始画面が原画獲得画面のため、初回に必ず通る
                using var sequence = ArtworkFragmentTutorialSequenceFactory.Create();
                await sequence.Play(_cancellationTokenSource.Token);
            }

            _isPlayingTutorialSequence = false;
        }

        public void Dispose()
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;
        }
    }
}
