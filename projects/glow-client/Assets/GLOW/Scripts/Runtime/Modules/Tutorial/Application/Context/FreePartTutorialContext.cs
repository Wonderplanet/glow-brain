using System;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Domain.Evaluator;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Modules.Tutorial.Presentation.Sequence.FreePart;
using GLOW.Scenes.AdventBattleMission.Domain.Evaluator;
using Zenject;

namespace GLOW.Modules.Tutorial.Application.Context
{
    public class FreePartTutorialContext : ITutorialFreePartContext, IDisposable, IFreePartTutorialPlayingStatus
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] PlaceholderFactory<ReleaseHardTutorialSequence> HardReleaseSequenceFactory { get; }
        [Inject] PlaceholderFactory<ReleaseEventQuestTutorialSequence> ReleaseEventQuestSequenceFactory { get; }
        [Inject] PlaceholderFactory<ReleasePvpTutorialSequence> ReleasePvpTutorialSequenceFactory { get; }
        [Inject] PlaceholderFactory<ReleaseAdventBattleTutorialSequence> ReleaseAdventBattleTutorialSequenceFactory { get; }
        [Inject] PlaceholderFactory<IdleIncentiveTutorialSequence> IdleIncentiveTutorialSequenceFactory { get; }
        [Inject] PlaceholderFactory<SpecialRoleTutorialSequence> SpecialRoleTutorialSequenceFactory { get; }
        [Inject] PlaceholderFactory<OutpostEnhanceTutorialSequence> OutpostEnhanceTutorialSequenceFactory { get; }
        [Inject] PlaceholderFactory<ReleaseEnhanceQuestTutorialSequence> ReleaseEnhanceQuestTutorialSequenceFactory { get; }
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IMstIdleIncentiveRepository MstIdleIncentiveRepository { get; }
        [Inject] IAdventBattleDateTimeEvaluator AdventBattleDateTimeEvaluator { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] CompleteFreePartTutorialUseCase CompleteFreePartTutorialUseCase { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }

        CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();
        PlayingTutorialSequenceFlag _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.False;

        PlayingTutorialSequenceFlag IFreePartTutorialPlayingStatus.IsPlayingTutorialSequence => _isPlayingTutorialSequence;

        public async UniTask<bool> DoIfTutorial(Func<UniTask> action)
        {
            var executed = await PlaySequenceIfNeeds();
            await action();
            return executed;
        }

        public void InterruptTutorial()
        {
            // チュートリアル中断
            _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.False;
            _cancellationTokenSource?.Cancel();
        }

        async UniTask<bool> PlaySequenceIfNeeds()
        {
            if (_isPlayingTutorialSequence) return false;

            var gameFetchOther = GameRepository.GetGameFetchOther();

            // メインパートチュートリアルが完了していない場合はスキップ
            if (gameFetchOther.TutorialStatus != TutorialSequenceIdDefinitions.TutorialMainPart_completeTutorial) return false;

            _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.True;

            var executed = false;

            // freePartが未達成(リストに無い)かつチュートリアル条件を満たしている場合にチュートリアル開始
            if (CanBeginTutorial(TutorialFreePartIdDefinitions.ReleaseEventQuest, CheckIsOpeningEventQuest))
            {
                // 1.いいジャン祭開放チュートリアル
                using var sequence = ReleaseEventQuestSequenceFactory.Create();
                await sequence.Play(_cancellationTokenSource.Token);
                executed = true;
            }
            else if (CanBeginTutorial(TutorialFreePartIdDefinitions.ReleaseAdventBattle) &&
                     GetActiveAdventBattleModel() is var model &&
                     !model.IsEmpty())
            {
                // 2.降臨バトル開放チュートリアル
                using var sequence = ReleaseAdventBattleTutorialSequenceFactory.Create();
                await sequence.Play(_cancellationTokenSource.Token);
                executed = true;
            }
            else if (CanBeginTutorial(TutorialFreePartIdDefinitions.ReleaseHardStage))
            {
                // 3.難易度開放チュートリアル
                using var hardReleaseSequence = HardReleaseSequenceFactory.Create();
                await hardReleaseSequence.Play(_cancellationTokenSource.Token);
                executed = true;
            }
            else if (CanBeginTutorial(TutorialFreePartIdDefinitions.ReleasePvp, IsOpeningPvp))
            {
                // 4.決闘遷移チュートリアル
                using var releasePvpTutorialSequence = ReleasePvpTutorialSequenceFactory.Create();
                await releasePvpTutorialSequence.Play(_cancellationTokenSource.Token);
                executed = true;
            }
            else if (CanBeginTutorial(TutorialFreePartIdDefinitions.ReleaseEnhanceQuest))
            {
                // 5.コイン獲得クエストチュートリアル(イベントコンテンツ誘導)
                using var sequence = ReleaseEnhanceQuestTutorialSequenceFactory.Create();
                await sequence.Play(_cancellationTokenSource.Token);
                executed = true;
            }
            else if (CanBeginTutorial(TutorialFreePartIdDefinitions.OutpostEnhance, ShouldStartOutpostEnhanceTutorial))
            {
                // 6.ゲート強化チュートリアル
                if (IsEnhancedOutpost())
                {
                    // 既にゲート強化をしている場合、チュートリアルを完了済みとする
                    await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(
                        _cancellationTokenSource.Token,
                        TutorialFreePartIdDefinitions.OutpostEnhance);
                }
                else
                {
                    using var sequence = OutpostEnhanceTutorialSequenceFactory.Create();
                    await sequence.Play(_cancellationTokenSource.Token);
                }

                // チュートリアルが完了したのでフラグをfalseにする
                SetFalseShouldStartOutpostEnhanceTutorial();
                executed = true;
            }
            else if (CanBeginTutorial(TutorialFreePartIdDefinitions.SpecialUnit, HasSpecialUnit))
            {
                // 7.スペシャルロールチュートリアル
                using var sequence = SpecialRoleTutorialSequenceFactory.Create();
                await sequence.Play(_cancellationTokenSource.Token);
                executed = true;
            }
            else if (CanBeginTutorial(TutorialFreePartIdDefinitions.IdleIncentive, CanReceiveIdleIncentive))
            {
                // 8.探索報酬チュートリアル
                using var sequence = IdleIncentiveTutorialSequenceFactory.Create();
                await sequence.Play(_cancellationTokenSource.Token);
                executed = true;
            }

            _isPlayingTutorialSequence = PlayingTutorialSequenceFlag.False;

            return executed;
        }

        public void Dispose()
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;
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

        // 開催中のいいジャン祭があるか
        bool CheckIsOpeningEventQuest()
        {
            var openingEvent = MstEventDataRepository.GetEvents()
                .FirstOrDefault(m => m.StartAt <= TimeProvider.Now && TimeProvider.Now < m.EndAt, MstEventModel.Empty);

            return !openingEvent.IsEmpty();
        }

        bool IsOpeningPvp()
        {
            var sysPvpSeasonModel = GameRepository.GetGameFetchOther().SysPvpSeasonModel;
            return CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                sysPvpSeasonModel.StartAt.Value,
                sysPvpSeasonModel.EndAt.Value);
        }

        // 開催中の降臨バトル情報取得
        MstAdventBattleModel GetActiveAdventBattleModel()
        {
            return AdventBattleDateTimeEvaluator.GetOpenedAdventBattleModel();
        }

        // 探索報酬が受け取れる時間が経過しているか
        bool CanReceiveIdleIncentive()
        {
            var idleStartedAt = GameRepository.GetGameFetchOther().UserIdleIncentiveModel.IdleStartedAt;
            var initialRewardReceiveMinutes = MstIdleIncentiveRepository.GetMstIdleIncentive().InitialRewardReceiveMinutes;

            return TimeProvider.Now - idleStartedAt >= initialRewardReceiveMinutes;
        }

        bool HasSpecialUnit()
        {
            // 所持ユニット
            var userUnits = GameRepository.GetGameFetchOther().UserUnitModels;

            // 全ユニット
            var mstCharacterModels = MstCharacterDataRepository.GetCharacters();

            // 所持ユニットのMstをJoin
            var ownedUnits = userUnits.Join(
                mstCharacterModels,
                unit => unit.MstUnitId,
                mst => mst.Id,
                (unit, mst) => (unit, mst));

            // 抽出したユニットの中にCharacterUnitRoleTypeがSpecialのものがあるか判定
            return ownedUnits.Any(unit => unit.mst.RoleType == CharacterUnitRoleType.Special);
        }

        bool IsEnhancedOutpost()
        {
            // ゲート強化をしたことがあった場合、チュートリアルを完了済みとする
            var outpostEnhanceModels = GameRepository.GetGameFetchOther().UserOutpostEnhanceModels;
            var isEnhanced = outpostEnhanceModels.Any(model => model.IsEnhanced());

            return isEnhanced;
        }

        bool ShouldStartOutpostEnhanceTutorial()
        {
            // 敗北したことがある場合、チュートリアル開始フラグが立っている
            return PreferenceRepository.ShouldStartOutpostEnhanceTutorial;
        }

        void SetFalseShouldStartOutpostEnhanceTutorial()
        {
            // ゲート強化チュートリアル開始フラグをfalseにする
            PreferenceRepository.ShouldStartOutpostEnhanceTutorial = false;
        }
    }
}
