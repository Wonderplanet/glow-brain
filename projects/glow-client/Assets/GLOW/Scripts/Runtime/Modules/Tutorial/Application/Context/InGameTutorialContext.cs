using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Modules.Tutorial.Presentation.Sequence;
using Zenject;

namespace GLOW.Modules.Tutorial.Application.Context
{
    public class InGameTutorialContext : ITutorialContext, IDisposable
    {
        [Inject] PlaceholderFactory<InGame1TutorialSequence> InGame1TutorialSequenceFactory { get; }
        [Inject] PlaceholderFactory<InGame2TutorialSequence> InGame2TutorialSequenceFactory { get; }
        [Inject] PlaceholderFactory<InGameIntroductionTutorialSequence> 
            InGameIntroductionTutorialSequenceFactory { get; }
        [Inject] IGameRepository GameRepository { get; }

        CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();
        PlayingTutorialSequenceFlag _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.False;
        
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

            _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.True;
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;

            if (tutorialStatus.IsStartInGame1())  // チュートリアルステージ(stageId: tutorial_2)
            {
                using var inGame1TutorialSequence = InGame1TutorialSequenceFactory.Create();
                await inGame1TutorialSequence.Play(_cancellationTokenSource.Token);
            }
            else if (tutorialStatus.IsStartInGame2())  // 1-2チュートリアル(stageId: tutorial_3)
            {
                using var inGame2TutorialSequence = InGame2TutorialSequenceFactory.Create();
                await inGame2TutorialSequence.Play(_cancellationTokenSource.Token);
            }

            _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.False;
        }

        public void Dispose()
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;
        }
    }
}
