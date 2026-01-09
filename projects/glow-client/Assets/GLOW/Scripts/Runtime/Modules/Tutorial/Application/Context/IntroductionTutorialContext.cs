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
    public class IntroductionTutorialContext : 
        IIntroductionTutorialContext,
        IDisposable
    {
        [Inject] PlaceholderFactory<IntroductionMangaSequence> IntroductionMangaSequenceFactory { get; }
        [Inject] PlaceholderFactory<InGameIntroductionTutorialSequence> InGameIntroductionTutorialSequenceFactory { get; }
        [Inject] IGameRepository GameRepository { get; }

        CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();
        PlayingTutorialSequenceFlag _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.False;

        public async UniTask DoIfPreLoadIntroductionTutorial(Func<UniTask> action)
        {
            await PreLoadTutorialIfNeeds(action);
        }
        
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
            
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            
            if (tutorialStatus.IsIntroduction())// 導入パート
            {
                _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.True;
                
                // 導入漫画中は待機
                using var introductionMangaSequence = IntroductionMangaSequenceFactory.Create();
                await introductionMangaSequence.Play(_cancellationTokenSource.Token);
                
                // ダウンロード表示を残したいのでシーケンスを開始し、インゲームの開始まで中で再生してシーケンス内で待機
                PlayInGameTutorialSequence().Forget();
            }
            
        }
        
        async UniTask PreLoadTutorialIfNeeds(Func<UniTask> action)
        {
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            
            if (tutorialStatus.IsIntroduction())// 導入パート
            {
                await action.Invoke();
            }
            
        }
        
        async UniTask PlayInGameTutorialSequence()
        {
            using var inGameIntroductionTutorialSequence = InGameIntroductionTutorialSequenceFactory.Create();
            await inGameIntroductionTutorialSequence.Play(_cancellationTokenSource.Token);
            
            // Forgetで呼び出しているので、ここまで来たら終了させる
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