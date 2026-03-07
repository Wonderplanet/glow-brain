using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Domain.Evaluator;
using GLOW.Modules.Tutorial.Presentation.Sequence.FreePart;
using Zenject;

namespace GLOW.Modules.Tutorial.Application.Context
{
    public class EventQuestTutorialContext : IEventQuestTutorialContext, IDisposable, IEventQuestTutorialPlayingStatus
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }
        [Inject] PlaceholderFactory<ReleaseEventQuestTutorialSequence> ReleaseEventQuestTutorialSequenceFactory { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }

        CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();
        PlayingTutorialSequenceFlag _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.False;

        PlayingTutorialSequenceFlag IEventQuestTutorialPlayingStatus.IsPlayingTutorialSequence => _isPlayingTutorialSequence;

        public async UniTask<bool> DoIfTutorial(Func<UniTask> action)
        {
            var executed = await PlaySequenceIfNeeds();
            await action();
            return executed;
        }

        async UniTask<bool> PlaySequenceIfNeeds()
        {
            if (_isPlayingTutorialSequence) return false;

            _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.True;

            var executed = false;

            // いいジャン祭画面で開始するため、開催期間チェックはしない
            if (CanBeginTutorial(TutorialFreePartIdDefinitions.ReleaseEventQuest))
            {
                // いいジャン祭 初遷移時チュートリアル
                using var releaseEventQuestTutorialSequence = ReleaseEventQuestTutorialSequenceFactory.Create();
                await releaseEventQuestTutorialSequence.Play(_cancellationTokenSource.Token);
                executed = true;
            }

            _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.False;

            return executed;
        }

        bool CanBeginTutorial(TutorialFunctionName functionName, Func<bool> functionCondition = null)
        {
            // 部分メンテ中だった場合はスキップ
            var contentMaintenanceTarget = TutorialFreePartIdEvaluator.GetContentMaintenanceTarget(functionName);
            if (CheckContentMaintenanceUseCase.IsInMaintenance(contentMaintenanceTarget)) return false;

            // 達成済みのフリーパートを取得
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var freeParts = gameFetchOther.UserTutorialFreePartModels;

            return TutorialEvaluator.CanBeginTutorial(
                MstTutorialRepository,
                GameRepository,
                freeParts,
                functionName,
                functionCondition);
        }

        public void Dispose()
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;
        }
    }
}

