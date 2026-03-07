using System;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PvpTop.Domain.Resolver;
using Zenject;

namespace GLOW.Scenes.QuestContentTop.Domain.Factory
{
    public class ContentNoticePvpFactory : IContentNoticePvpFactory
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IContentTopAccessPreferenceRepository ContentTopAccessPreferenceRepository { get; }
        [Inject] IPvpQuestContentOpeningStatusModelFactory PvpQuestContentOpeningStatusModelFactory { get; }
        [Inject] IPvpChallengeStatusFactory PvpChallengeStatusFactory { get; }
        [Inject] IMstCurrentPvpModelResolver MstCurrentPvpModelResolver { get; }

        NotificationBadge IContentNoticePvpFactory.Create()
        {
            var lastAccessedAt = ContentTopAccessPreferenceRepository.GetLastAccessTime();

            return new NotificationBadge(
                HasNewPvp(lastAccessedAt) ||
                IsPvpChallengeable() ||
                IsPvpChallengeCountReset(lastAccessedAt));
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
