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
    public class TutorialContext : ITutorialContext, IDisposable, ITutorialPlayingStatus
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] PlaceholderFactory<MainPart1TutorialSequence> MainPart1TutorialSequenceFactory { get; }
        CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();
        PlayingTutorialSequenceFlag _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.False;
        
        PlayingTutorialSequenceFlag ITutorialPlayingStatus.IsPlayingTutorialSequence => _isPlayingTutorialSequence;

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
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            
            // 再生中や完了済みの場合はスキップ
            if (_isPlayingTutorialSequence || tutorialStatus.IsCompleted()) return;

            _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.True;
            
            if (tutorialStatus.IsMainPart1())
            {
                using var mainPart1TutorialSequence = MainPart1TutorialSequenceFactory.Create();
                await mainPart1TutorialSequence.Play(_cancellationTokenSource.Token);
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
