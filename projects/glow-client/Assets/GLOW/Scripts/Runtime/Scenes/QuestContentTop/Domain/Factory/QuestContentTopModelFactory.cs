using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Domain.ValueObjects.QuestContent;
using GLOW.Core.Extensions;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Scenes.EventQuestSelect.Domain.ValueObject;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.QuestContentTop.Domain.Factory;
using GLOW.Scenes.QuestContentTop.Domain.UseCaseModel;
using GLOW.Scenes.QuestContentTop.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.QuestContentTop.Domain
{
    public class QuestContentTopModelFactory : IQuestContentTopModelFactory
    {
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }

        IReadOnlyList<QuestContentTopElementUseCaseModel>
            IQuestContentTopModelFactory.CreateEventQuestContentTopItemUseCaseModels()
        {
            return MstEventDataRepository.GetEvents()
                .Where(m =>CalculateTimeCalculator.IsValidTime(TimeProvider.Now, m.StartAt ,m.EndAt))
                .OrderByDescending(m => m.StartAt)
                .Select(CreateEventQuestModel)
                .ToList();
        }

        QuestContentTopElementUseCaseModel CreateEventQuestModel(MstEventModel mstEventModel)
        {
            var badgeModel = GameRepository.GetGameFetch().BadgeModel;
            var eventMissionBadge = new NotificationBadge(!badgeModel.UnreceivedMissionEventRewardCountById(mstEventModel.Id).IsZero());

            var limitTimeSpan = CalculateTimeCalculator.GetRemainingTime(TimeProvider.Now, mstEventModel.EndAt);

            var campaignModels = GetCampaignModels(mstEventModel.Id);

            return new QuestContentTopElementUseCaseModel(
                QuestContentTopElementType.Event,
                new QuestContentOpeningStatusModel(
                    QuestContentOpeningStatusAtTimeType.Opening,
                    QuestContentOpeningStatusAtUserStatus.None,
                    QuestContentReleaseRequiredSentence.Empty),
                EventChallengeCount.Empty,
                QuestContentTopChallengeType.Normal,
                QuestChallengeResetTime.Empty,
                limitTimeSpan,
                HasRankingFlag.False,
                NotificationBadge.False,
                eventMissionBadge,
                mstEventModel.Id,
                mstEventModel.Name,
                EventContentBannerAssetPath.FromAssetKey(mstEventModel.AssetKey),
                campaignModels
            );
        }

        List<CampaignModel> GetCampaignModels(MasterDataId mstEventId)
        {
            var quests = MstQuestDataRepository.GetMstQuestModelsFromEvent(mstEventId);

            var result = new List<CampaignModel>();
            foreach (var quest in quests)
            {
                var campaignModels = CampaignModelFactory.CreateCampaignModels(
                    quest.Id,
                    CampaignTargetType.EventQuest,
                    CampaignTargetIdType.Quest,
                    quest.Difficulty);
                result.AddRange(campaignModels);
            }

            return result;
        }
        IReadOnlyList<QuestContentTopElementUseCaseModel>
            IQuestContentTopModelFactory.CreateEnhanceQuestContentTopItemUseCaseModels()
        {
            return MstQuestDataRepository.GetMstQuestModels()
                .Where(m => m.QuestType == QuestType.Enhance)
                .Where(m => m.StartDate <= TimeProvider.Now && TimeProvider.Now < m.EndDate)
                .SelectMany(m =>
                {
                    var mstStages = MstStageDataRepository.GetMstStagesFromMstQuestId(m.Id);
                    return mstStages.Select(s => CreateEnhanceQuestModel(m , s));
                })
                .ToList();
        }

        QuestContentTopElementUseCaseModel CreateEnhanceQuestModel(MstQuestModel mstQuestModel, MstStageModel mstStageModel)
        {
            var gameFetch = GameRepository.GetGameFetch();

            var userStageEnhanceModel = gameFetch.UserStageEnhanceModels
                .FirstOrDefault(u => u.MstStageId == mstStageModel.Id, UserStageEnhanceModel.Empty);

            var challengeLimitCount = MstConfigRepository.GetConfig(MstConfigKey.EnhanceQuestChallengeLimit).Value.ToInt();
            var challengeAdLimitCount = MstConfigRepository.GetConfig(MstConfigKey.EnhanceQuestChallengeAdLimit).Value.ToInt();

            // リセット時間
            TimeSpan resetTimeSpan = DailyResetTimeCalculator.GetRemainingTimeToDailyReset();

            var campaignModels = CampaignModelFactory.CreateCampaignModels(
                mstQuestModel.Id,
                CampaignTargetType.EnhanceQuest,
                CampaignTargetIdType.Quest,
                mstQuestModel.Difficulty);
            var challengeCountCampaignModel = CampaignModelFactory.CreateCampaignModel(
                mstQuestModel.Id,
                CampaignTargetType.EnhanceQuest,
                CampaignTargetIdType.Quest,
                mstQuestModel.Difficulty,
                CampaignType.ChallengeCount);
            if (!challengeCountCampaignModel.IsEmpty())
            {
                challengeLimitCount += challengeCountCampaignModel.EffectValue.Value;
            }

            if (userStageEnhanceModel.IsEmpty())
            {
                return new QuestContentTopElementUseCaseModel(
                    QuestContentTopElementType.Enhance,
                    new QuestContentOpeningStatusModel(
                        QuestContentOpeningStatusAtTimeType.Opening,
                        QuestContentOpeningStatusAtUserStatus.None,
                        QuestContentReleaseRequiredSentence.Empty),
                    new EnhanceQuestChallengeCount(challengeLimitCount),
                    QuestContentTopChallengeType.Normal,
                    new QuestChallengeResetTime(resetTimeSpan),
                    new RemainingTimeSpan(resetTimeSpan),
                    HasRankingFlag.False,
                    NotificationBadge.False,
                    NotificationBadge.False,
                    MasterDataId.Empty,
                    EventName.Empty,
                    EventContentBannerAssetPath.Empty,
                    campaignModels
                );
            }


            var challengeLeftCount = challengeLimitCount - userStageEnhanceModel.ResetChallengeCount.Value;
            var adChallengeLeftCount = challengeAdLimitCount - userStageEnhanceModel.ResetAdChallengeCount.Value;

            var challengeCount = challengeLeftCount == 0
                ? new EnhanceQuestChallengeCount(adChallengeLeftCount)
                : new EnhanceQuestChallengeCount(challengeLeftCount);

            var challengeType = challengeLeftCount == 0 && adChallengeLeftCount != 0
                ? QuestContentTopChallengeType.Ad
                : QuestContentTopChallengeType.Normal;

            var isPlayable = challengeCount.IsEnough();

            var openingStatusAtUser = isPlayable
                ? QuestContentOpeningStatusAtUserStatus.None
                : QuestContentOpeningStatusAtUserStatus.OverLimitChallengeCount;

            return new QuestContentTopElementUseCaseModel(
                QuestContentTopElementType.Enhance,
                new QuestContentOpeningStatusModel(
                    QuestContentOpeningStatusAtTimeType.Opening,
                    openingStatusAtUser,
                    QuestContentReleaseRequiredSentence.Empty),
                challengeCount,
                challengeType,
                new QuestChallengeResetTime(resetTimeSpan),
                new RemainingTimeSpan(resetTimeSpan),
                HasRankingFlag.False,
                NotificationBadge.False,
                NotificationBadge.False,
                MasterDataId.Empty,
                EventName.Empty,
                EventContentBannerAssetPath.Empty,
                campaignModels
            );
        }

        /// <summary>
        /// 一致する降臨バトルのUseCaseModelの中から１つだけ返す
        /// １つだけ返す理由は、降臨バトルチュートリアルが現状開催中の降臨バトルを前提にしているため。
        /// 理想としては複数あっても、開催中を見てフォーカスするようにチュートリアル側を修正する必要がある。
        /// </summary>
        /// <returns></returns>
        IReadOnlyList<QuestContentTopElementUseCaseModel>
            IQuestContentTopModelFactory.CreateAdventBattleUseCaseModelsWithBeforeOpen()
        {
            var adventBattles = MstAdventBattleDataRepository.GetMstAdventBattleModels()
                .Where(m => TimeProvider.Now >= m.StartDateTime.Value)
                .ToList();
            if(!adventBattles.Any())
            {
                // 現在開催中/開催済みのものがない場合は、開始前のものを取得
                var adventBattlesBeforeOpen = MstAdventBattleDataRepository.GetMstAdventBattleModels()
                    .Where(m => TimeProvider.Now < m.StartDateTime.Value)
                    .ToList();
                if (adventBattlesBeforeOpen.Any())
                {
                    return adventBattlesBeforeOpen
                        .OrderBy(m => m.StartDateTime.Value)
                        .Select(CreateQuestContentTopElementUseCaseModel)
                        .Take(1)
                        .ToList();
                }
                return new List<QuestContentTopElementUseCaseModel>();
            }

            if (adventBattles.Exists(x => CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now,
                    x.StartDateTime.Value,
                    x.EndDateTime.Value)))
            {
                // 開催中のものがある場合は、開催中のものを返す
                return adventBattles
                    .Where(m => CalculateTimeCalculator.IsValidTime(
                        TimeProvider.Now,
                        m.StartDateTime.Value,
                        m.EndDateTime.Value))
                    .OrderBy(m => m.StartDateTime.Value)
                    .Select(CreateQuestContentTopElementUseCaseModel)
                    .Take(1)
                    .ToList();
            }

            // 開催中のものがない場合は開催済み、もしくは開始前のものを返す
            return adventBattles
                .OrderBy(m => m.StartDateTime.Value)
                .Select(CreateQuestContentTopElementUseCaseModel)
                .Take(1)
                .ToList();
        }

        QuestContentTopElementUseCaseModel CreateQuestContentTopElementUseCaseModel(MstAdventBattleModel targetAdventBattle)
        {
            var gameFetch = GameRepository.GetGameFetch();
            var userAdventBattleModel = gameFetch.UserAdventBattleModels
                .FirstOrDefault(model => model.MstAdventBattleId == targetAdventBattle.Id, UserAdventBattleModel.Empty);
            var limitTimeSpan = CalculateTimeCalculator.GetRemainingTime(TimeProvider.Now, targetAdventBattle.EndDateTime.Value);
            var adChallengeableCount = targetAdventBattle.AdChallengeCount - userAdventBattleModel.ResetAdChallengeCount;

            // リセット時間
            TimeSpan resetTimeSpan = DailyResetTimeCalculator.GetRemainingTimeToDailyReset();

            var adventBattleMissionBadge = CreateQuestContentTopElementUseCaseModel(
                targetAdventBattle.StartDateTime,
                targetAdventBattle.EndDateTime,
                gameFetch.BadgeModel.UnreceivedMissionAdventBattleRewardCount);

            var campaignModels = CampaignModelFactory.CreateCampaignModels(
                MasterDataId.Empty,
                CampaignTargetType.AdventBattle,
                CampaignTargetIdType.Quest,
                Difficulty.Normal);
            var challengeCountCampaignModel = CampaignModelFactory.CreateCampaignModel(
                MasterDataId.Empty,
                CampaignTargetType.AdventBattle,
                CampaignTargetIdType.Quest,
                Difficulty.Normal,
                CampaignType.ChallengeCount);

            var challengeableCount = GetAdventBattleChallengeCount(
                targetAdventBattle.ChallengeCount,
                userAdventBattleModel.ResetChallengeCount,
                challengeCountCampaignModel);

            var openStatus = GetAdventBattleOpeningStatus(
                targetAdventBattle.StartDateTime,
                targetAdventBattle.EndDateTime,
                challengeableCount,
                adChallengeableCount,
                gameFetch.UserParameterModel.Level);

            var challengeType = challengeableCount.IsZero() && !adChallengeableCount.IsZero()
                ? QuestContentTopChallengeType.Ad
                : QuestContentTopChallengeType.Normal;

            return new QuestContentTopElementUseCaseModel(
                QuestContentTopElementType.AdventBattle,
                openStatus,
                challengeableCount.IsZero() ? adChallengeableCount : challengeableCount,
                challengeType,
                new QuestChallengeResetTime(resetTimeSpan),
                CreateAdventBattleRemainingTimeSpan(openStatus.OpeningStatusAtTimeType, targetAdventBattle),
                HasRankingFlag.True,
                NotificationBadge.False,//TODO: ランキングで通知あれば出す
                adventBattleMissionBadge,
                MasterDataId.Empty,
                EventName.Empty,
                EventContentBannerAssetPath.Empty,
                campaignModels
                );
        }

        NotificationBadge CreateQuestContentTopElementUseCaseModel(
            AdventBattleStartDateTime startDateTime,
            AdventBattleEndDateTime endDateTime,
            UnreceivedMissionRewardCount unreceivedMissionAdventBattleRewardCount
            )
        {
            var isOpening = CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                startDateTime.Value,
                endDateTime.Value);

            return new NotificationBadge(
                isOpening &&
                !unreceivedMissionAdventBattleRewardCount.IsZero());
        }

        RemainingTimeSpan CreateAdventBattleRemainingTimeSpan(
            QuestContentOpeningStatusAtTimeType statusAtTimeType,
            MstAdventBattleModel targetAdventBattle)
        {
            if (statusAtTimeType == QuestContentOpeningStatusAtTimeType.BeforeOpen)
            {
                return CalculateTimeCalculator.GetRemainingTime(TimeProvider.Now, targetAdventBattle.StartDateTime.Value);
            }

            return CalculateTimeCalculator.GetRemainingTime(TimeProvider.Now, targetAdventBattle.EndDateTime.Value);
        }

        AdventBattleChallengeCount GetAdventBattleChallengeCount(
            AdventBattleChallengeCount baseChallengeableCount,
            AdventBattleChallengeCount userResetChallengeCount,
            CampaignModel campaignModel)
        {
            var result = baseChallengeableCount;

            // キャンペーンの効果値を加算
            if (!campaignModel.IsEmpty() && campaignModel.IsChallengeCountCampaign())
            {
                result += campaignModel.EffectValue;
            }
            // ユーザーの挑戦回数を引く
            result -= userResetChallengeCount;

            return result;
        }

        QuestContentOpeningStatusModel GetAdventBattleOpeningStatus(
            AdventBattleStartDateTime startDateTime,
            AdventBattleEndDateTime endDateTime,
            AdventBattleChallengeCount challengeableCount,
            AdventBattleChallengeCount adChallengeableCount,
            UserLevel userLevel)
        {
            var adventBattleTutorialModel = MstTutorialRepository.GetMstTutorialModels()
                .FirstOrDefault(m => m.TutorialFunctionName == TutorialFreePartIdDefinitions.ReleaseAdventBattle,
                    MstTutorialModel.Empty);
            var releaseRequiredUserLevel = adventBattleTutorialModel.IsEmpty() ?
                UserLevel.Empty :
                adventBattleTutorialModel.ConditionValue.ToUserLevel();

            return new QuestContentOpeningStatusModel(
                GetTimeStatus(startDateTime, endDateTime),
                GetUserStatus(challengeableCount, adChallengeableCount, userLevel),
                CreateQuestContentReleaseRequiredSentence(releaseRequiredUserLevel)
                );
        }

        QuestContentOpeningStatusAtTimeType GetTimeStatus(
            AdventBattleStartDateTime startDateTime,
            AdventBattleEndDateTime endDateTime)
        {
            // OutOfLimitは早期returnで行っているのでここでは不要
            if (TimeProvider.Now < startDateTime.Value)
            {
                return QuestContentOpeningStatusAtTimeType.BeforeOpen;
            }

            // 集計期間の終了期限
            var configModel = MstConfigRepository.GetConfig(MstConfigKey.AdventBattleRankingAggregateHours);
            var aggregateHours = configModel.IsEmpty()
                ? AdventBattleConst.DefaultAdventBattleRankingAggregateHours
                : configModel.Value.ToInt();
            var totalizingEndDateTime = endDateTime.Value.AddHours(aggregateHours);

            if (CalculateTimeCalculator.IsValidTime(TimeProvider.Now, endDateTime.Value, totalizingEndDateTime))
            {
                // 集計中
                return QuestContentOpeningStatusAtTimeType.Totalizing;
            }

            if (TimeProvider.Now >= totalizingEndDateTime)
            {
                // 開催終了
                return QuestContentOpeningStatusAtTimeType.OutOfLimit;
            }

            return QuestContentOpeningStatusAtTimeType.Opening;
        }

        QuestContentOpeningStatusAtUserStatus GetUserStatus(
            AdventBattleChallengeCount challengeableCount,
            AdventBattleChallengeCount adChallengeableCount,
            UserLevel userLevel)
        {
            if(!IsReleaseUserLevelAtAdventBattle(userLevel))
            {
                return QuestContentOpeningStatusAtUserStatus.RankLocked;
            }
            if(challengeableCount.IsZero() && adChallengeableCount.IsZero())
            {
                return QuestContentOpeningStatusAtUserStatus.OverLimitChallengeCount;
            }

            return QuestContentOpeningStatusAtUserStatus.None;
        }

        QuestContentReleaseRequiredSentence CreateQuestContentReleaseRequiredSentence(UserLevel requiredUserLevel)
        {
            if (!requiredUserLevel.IsEmpty())
            {
                return QuestContentReleaseRequiredSentenceFactory.Create(requiredUserLevel);
            }
            else
            {
                return QuestContentReleaseRequiredSentence.Empty;
            }
        }

        bool IsReleaseUserLevelAtAdventBattle(UserLevel userLevel)
        {
            var adventBattleTutorialModel = MstTutorialRepository.GetMstTutorialModels()
                .FirstOrDefault(m => m.TutorialFunctionName == TutorialFreePartIdDefinitions.ReleaseAdventBattle,
                    MstTutorialModel.Empty);
            if (adventBattleTutorialModel.IsEmpty())
            {
                return true;
            }
            return adventBattleTutorialModel.ConditionValue.ToUserLevel() <= userLevel;
        }
    }
}
