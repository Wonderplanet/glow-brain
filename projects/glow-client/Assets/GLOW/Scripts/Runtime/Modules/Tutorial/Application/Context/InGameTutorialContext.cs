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
            // NOTE:チュートリアル短縮で不要になった場合は削除
            
            
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            if (_isPlayingTutorialSequence || tutorialStatus.IsCompleted()) return;

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
