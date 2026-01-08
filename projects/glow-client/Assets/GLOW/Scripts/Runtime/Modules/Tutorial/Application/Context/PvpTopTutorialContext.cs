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
    public class PvpTopTutorialContext : IPvpTopTutorialContext, IDisposable, IPvpTutorialPlayingStatus
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }
        [Inject] PlaceholderFactory<TransitPvpTutorialSequence> TransitPvpTutorialSequenceFactory { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }
        CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();
        PlayingTutorialSequenceFlag _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.False;
        
        PlayingTutorialSequenceFlag IPvpTutorialPlayingStatus.IsPlayingTutorialSequence => _isPlayingTutorialSequence;

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

            if (CanBeginTutorial(TutorialFreePartIdDefinitions.TransitPvp))
            {
                // ランクマッチ 初遷移時チュートリアル
                using var transitPvpTutorialSequence = TransitPvpTutorialSequenceFactory.Create();
                await transitPvpTutorialSequence.Play(_cancellationTokenSource.Token);
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
