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
    public class ArtworkEffectTutorialContext :
        IArtworkEffectTutorialContext,
        IArtworkEffectTutorialPlayingStatus,
        IDisposable
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }
        [Inject] PlaceholderFactory<TransitArtworkEffectTutorialSequence> TransitArtworkEffectTutorialSequenceFactory { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }


        [Inject] ITutorialPlayingStatus TutorialPlayingStatus { get; }
        [Inject] IFreePartTutorialPlayingStatus FreePartTutorialPlayingStatus { get; }
        [Inject] IPvpTutorialPlayingStatus PvpTutorialPlayingStatus { get; }
        [Inject] IEventQuestTutorialPlayingStatus EventQuestTutorialPlayingStatus { get; }
        [Inject] IArtworkEffectTutorialPlayingStatus ArtworkEffectTutorialPlayingStatus { get; }
        [Inject] IReleaseHardTutorialPlayingStatus ReleaseHardTutorialPlayingStatus { get; }

        PlayingTutorialSequenceFlag _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.False;
        PlayingTutorialSequenceFlag IArtworkEffectTutorialPlayingStatus.IsPlayingTutorialSequence => _isPlayingTutorialSequence;
        CancellationTokenSource _cancellationTokenSource = new ();

        async UniTask<bool> IArtworkEffectTutorialContext.DoIfTutorial(Func<UniTask> action)
        {
            var executed = await PlaySequenceIfNeeds();
            await action();
            return executed;
        }

        async UniTask<bool> PlaySequenceIfNeeds()
        {
            if (_isPlayingTutorialSequence) return false;

            var playingTutorialSequenceFlag = PlayingTutorialSequenceEvaluator.IsPlayingTutorial(
                TutorialPlayingStatus,
                FreePartTutorialPlayingStatus,
                PvpTutorialPlayingStatus,
                EventQuestTutorialPlayingStatus,
                ArtworkEffectTutorialPlayingStatus,
                ReleaseHardTutorialPlayingStatus);
            if (playingTutorialSequenceFlag) return false;

            _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.True;

            var executed = false;

            if (CanBeginTutorial(TutorialFreePartIdDefinitions.TransitArtworkEffect))
            {
                // 初遷移時チュートリアル
                using var tutorialSequence = TransitArtworkEffectTutorialSequenceFactory.Create();
                await tutorialSequence.Play(_cancellationTokenSource.Token);
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

        void IDisposable.Dispose()
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;
        }
    }
}
