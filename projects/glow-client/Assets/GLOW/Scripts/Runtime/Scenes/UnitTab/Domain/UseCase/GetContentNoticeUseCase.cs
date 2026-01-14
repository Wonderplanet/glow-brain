using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.PvpTop.Domain.Resolver;
using GLOW.Scenes.QuestContentTop.Domain.Factory;
using UnityEngine;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.UnitTab.Domain.UseCase
{
    public class GetContentNoticeUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IContentTopAccessPreferenceRepository ContentTopAccessPreferenceRepository { get; }
        [Inject] IPvpQuestContentOpeningStatusModelFactory PvpQuestContentOpeningStatusModelFactory { get; }
        [Inject] IPvpChallengeStatusFactory PvpChallengeStatusFactory { get; }
        [Inject] IMstCurrentPvpModelResolver MstCurrentPvpModelResolver { get; }

        public NotificationBadge GetContentNotification()
        {
            // Note: 赤バッジ表示ルール
            // 最後に訪れたときから、新規追加や更新が発生している
            // - Event, EnhanceQuest, AdventBattle, Pvp
            // 最後に訪れたときから、挑戦回数リセットされている
            // - EnhanceQuest, AdventBattle, Pvp

            if (!ContentTopAccessPreferenceRepository.HasValue)
            {
                return NotificationBadge.True;
            }

            var lastAccessedAt = ContentTopAccessPreferenceRepository.GetLastAccessTime();
            var openingAdventBattleModels = MstAdventBattleDataRepository
                .GetMstAdventBattleModels()
                .Where(m => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, m.StartDateTime.Value, m.EndDateTime.Value))
                .ToList();
            return new NotificationBadge(
                HasNewEventContent(lastAccessedAt) ||
                IsCoinQuestChallengeCountReset(lastAccessedAt) ||
                HasNewMstAdventBattle(lastAccessedAt,openingAdventBattleModels) ||
                IsAdventBattleChallengeCountReset(lastAccessedAt, openingAdventBattleModels) ||
                HasNewPvp(lastAccessedAt) ||
                IsPvpChallengeable() ||
                IsPvpChallengeCountReset(lastAccessedAt));
        }

        bool HasNewEventContent(DateTimeOffset lastAccessedAt)
        {
            var mstEventModels = MstEventDataRepository.GetEvents()
                .Where(m => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, m.StartAt, m.EndAt))
                .ToList();

            //新規Eventあるか
            var hasNewEvent = mstEventModels.Exists(m => m.StartAt <= TimeProvider.Now && lastAccessedAt <= m.StartAt);
            //追加Stageあるか
            var hasNewStage = mstEventModels
                .SelectMany(m => MstQuestDataRepository.GetMstQuestModelsFromEvent(m.Id))
                .SelectMany(m => MstStageDataRepository.GetMstStagesFromMstQuestId(m.Id))
                .Exists(m => m.StartAt <= TimeProvider.Now && lastAccessedAt <= m.StartAt);

            return hasNewEvent || hasNewStage;
        }

        bool IsCoinQuestChallengeCountReset(DateTimeOffset lastAccessedAt)
        {
            var openingEnhanceQuests = MstQuestDataRepository.GetMstQuestModels()
                .Where(m => m.QuestType == QuestType.Enhance)
                .Where(m => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, m.StartDate, m.EndDate))
                .ToList();

            return openingEnhanceQuests.Any() && DailyResetTimeCalculator.IsPastDailyRefreshTime(lastAccessedAt);
        }

        bool HasNewMstAdventBattle(DateTimeOffset lastAccessedAt, IReadOnlyList<MstAdventBattleModel> openingAdventBattleModel)
        {
            return openingAdventBattleModel
                .Exists(m => m.StartDateTime.Value <= TimeProvider.Now && lastAccessedAt <= m.StartDateTime.Value);
        }

        bool IsAdventBattleChallengeCountReset(DateTimeOffset lastAccessedAt, List<MstAdventBattleModel> openingAdventBattleModel)
        {
            return openingAdventBattleModel.Any() && DailyResetTimeCalculator.IsPastDailyRefreshTime(lastAccessedAt);
        }

        bool HasNewPvp(DateTimeOffset lastAccessedAt)
        {
            var pvpStartAt = GameRepository.GetGameFetchOther().SysPvpSeasonModel.StartAt;
            return lastAccessedAt <= pvpStartAt.Value && pvpStartAt.Value <= TimeProvider.Now;
        }

        bool IsPvpChallengeable()
        {
            var seasonModel = GameRepository.GetGameFetchOther().SysPvpSeasonModel;
            var userPvpStatusModel = GameRepository.GetGameFetchOther().UserPvpStatusModel;
            var mstPvpModel = MstCurrentPvpModelResolver.CreateMstPvpModel(seasonModel.Id);

            var openingStatusModel = PvpQuestContentOpeningStatusModelFactory.Create();
            var pvpChallengeStatus =
                PvpChallengeStatusFactory.Create(mstPvpModel.ItemChallengeCost, userPvpStatusModel);

            return openingStatusModel.IsOpening() && pvpChallengeStatus.IsChallengeable();
        }

        bool IsPvpChallengeCountReset(DateTimeOffset lastAccessedAt)
        {
            DateTimeOffset pvpStartAt = GameRepository.GetGameFetchOther().SysPvpSeasonModel.StartAt.Value;

            if (pvpStartAt.Date == TimeProvider.Now.Date)
            {
                //開催初日はStartから比較
                return lastAccessedAt <= pvpStartAt && pvpStartAt <= TimeProvider.Now;
            }

            return DailyResetTimeCalculator.IsPastDailyRefreshTime(lastAccessedAt);
        }
    }
}
