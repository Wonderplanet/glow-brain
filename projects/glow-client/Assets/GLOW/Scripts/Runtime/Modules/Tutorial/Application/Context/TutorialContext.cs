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
        [Inject] PlaceholderFactory<MainPart2TutorialSequence> MainPart2TutorialSequenceFactory { get; }
        [Inject] PlaceholderFactory<MainPart3TutorialSequence> MainPart3TutorialSequenceFactory { get; }
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
            if (_isPlayingTutorialSequence) return;

            _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.True;
            // ここで分岐
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;


            if (tutorialStatus.IsMainPart1())
            {
                using var mainPart1TutorialSequence = MainPart1TutorialSequenceFactory.Create();
                await mainPart1TutorialSequence.Play(_cancellationTokenSource.Token);
            }
            else if (tutorialStatus.IsMainPart2())
            {
                using var mainPart2TutorialSequence = MainPart2TutorialSequenceFactory.Create();
                await mainPart2TutorialSequence.Play(_cancellationTokenSource.Token);
            }
            else if (tutorialStatus.IsMainPart3())
            {
                using var mainPart3TutorialSequence = MainPart3TutorialSequenceFactory.Create();
                await mainPart3TutorialSequence.Play(_cancellationTokenSource.Token);
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
