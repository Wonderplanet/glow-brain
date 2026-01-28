using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Constants.LocalNotification;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.GachaList.Domain.Evaluator;
using Zenject;

namespace GLOW.Core.Domain.Modules.LocalNotification
{
    public class LocalNotificationScheduler : ILocalNotificationScheduler
    {
        [Inject] ILocalNotifier LocalNotifier { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IMissionCacheRepository MissionCacheRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] IUserPropertyRepository UserPropertyRepository { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }
        [Inject] IMstMissionDailyDataRepository MstMissionDailyDataRepository { get; }
        [Inject] IGachaEvaluator GachaEvaluator { get; }

        Dictionary<LocalNotificationType, LocalNotificationIdentifier> _scheduleIdentifiers = new();

        public void Initialize()
        {
            // 通知スケジュールが端末に保存されている場合は取得
            _scheduleIdentifiers = PreferenceRepository.LocalNotificationScheduledIdentifiers;
        }

        public void RefreshIdleIncentiveSchedule()
        {
            var idleStartedAt = GameRepository.GetGameFetchOther().UserIdleIncentiveModel.IdleStartedAt;

            var targetHours = MstConfigRepository.GetConfig(MstConfigKey.LocalNotificationIdleIncentiveHours).Value.ToInt();
            var targetTime = idleStartedAt.AddHours(targetHours);
            if (targetTime <= TimeProvider.Now)
            {
                RemoveSchedule(LocalNotificationType.IdleIncentive);
                return;
            }

            RefreshSchedule(
                LocalNotificationType.IdleIncentive,
                "探索報酬が貯まってるよ！\nログインして探索を確認してみよう！",
                targetTime);
        }

        public void RefreshDailyMissionSchedule()
        {
            var missionModel = MissionCacheRepository.GetMissionModel();
            if (missionModel == null)
            {
                RemoveSchedule(LocalNotificationType.DailyMission);
                return;
            }
            // UserMissionBonusPointModelsには Daily, Weekly, Monthly の１つずつしか存在しない
            // Dailyが２つ以上存在することはない
            var dailyMissionModel = missionModel.UserMissionBonusPointModels
                .FirstOrDefault(mst => mst.MissionType == MissionType.Daily, UserMissionBonusPointModel.Empty);
            var mostReceivedRewardPointMission = dailyMissionModel.ReceivedRewardPoints.Any() ?
                dailyMissionModel.ReceivedRewardPoints.MaxBy(x => x.Value) :
                null;
            var mstMostCriterionCountMission = MstMissionDailyDataRepository.GetMstMissionDailyModels().Any() ?
                MstMissionDailyDataRepository.GetMstMissionDailyModels()
                    .Where(x => x.CriterionType == MissionCriterionType.MissionBonusPoint)
                    .MaxBy(x => x.CriterionCount) :
                null;
            if (mostReceivedRewardPointMission != null && mstMostCriterionCountMission != null)
            {
                // 受け取った報酬ポイントが最大のミッションのクリア条件を満たしているかチェック
                if (mostReceivedRewardPointMission.Value >= mstMostCriterionCountMission.CriterionCount.Value)
                {
                    RemoveSchedule(LocalNotificationType.DailyMission);
                    return;
                }
            }

            var targetHours = MstConfigRepository.GetConfig(MstConfigKey.LocalNotificationDailyMissionHours).Value.ToInt();
            var targetTime = GetTargetTime(targetHours);

            RefreshSchedule(
                LocalNotificationType.DailyMission,
                "デイリーミッションを確認しよう！",
                targetTime);
        }

        public void RefreshRemainCoinQuestSchedule()
        {
            var userStageEnhanceModel = GetCoinQuest();
            if (userStageEnhanceModel == null)
            {
                RemoveSchedule(LocalNotificationType.RemainCoinQuest);
                return;
            }

            var targetHours = MstConfigRepository.GetConfig(MstConfigKey.LocalNotificationCoinQuestHours).Value.ToInt();
            var targetTime = GetTargetTime(targetHours);

            var challengeLimitCount = MstConfigRepository.GetConfig(MstConfigKey.EnhanceQuestChallengeLimit).Value.ToInt();
            var campaignModel = CampaignModelFactory.CreateCampaignModel(
                MasterDataId.Empty,
                CampaignTargetType.EnhanceQuest,
                CampaignTargetIdType.Quest,
                Difficulty.Normal,
                CampaignType.ChallengeCount);
            if (!campaignModel.IsEmpty())
            {
                challengeLimitCount += campaignModel.EffectValue.Value;
            }

            var challengeLeftCount = challengeLimitCount - userStageEnhanceModel.ResetChallengeCount.Value;
            var challengeAdLimitCount = MstConfigRepository.GetConfig(MstConfigKey.EnhanceQuestChallengeAdLimit).Value.ToInt();
            var adChallengeLeftCount = challengeAdLimitCount - userStageEnhanceModel.ResetAdChallengeCount.Value;
            var totalChallengeLeftCount = challengeLeftCount + adChallengeLeftCount;
            // バトル前に通知を飛ばしているので1以下で判定する
            if (totalChallengeLeftCount <= 1)
            {
                RemoveSchedule(LocalNotificationType.RemainCoinQuest);
                return;
            }

            RefreshSchedule(
                LocalNotificationType.RemainCoinQuest,
                "コイン獲得クエストにまだ挑戦できるよ！\n今すぐ挑戦してコインを集めよう！",
                targetTime);
        }

        DateTimeOffset GetTargetTime(int targetHour)
        {
            var utcNow = TimeProvider.Now;
            TimeSpan jpOffset = new TimeSpan(9, 0, 0);
            var jstNow = utcNow.ToOffset(jpOffset);
            DateTimeOffset targetTime;
            if (jstNow.Hour >= targetHour)
            {
                var nextDay = jstNow.AddDays(1);
                targetTime = new DateTimeOffset(nextDay.Year, nextDay.Month, nextDay.Day, targetHour, 0, 0, jstNow.Offset);
            }
            else
            {
                targetTime = new DateTimeOffset(jstNow.Year, jstNow.Month, jstNow.Day, targetHour, 0, 0, jstNow.Offset);
            }

            return targetTime;
        }

        UserStageEnhanceModel GetCoinQuest()
        {
            var userStageEnhanceModel = GameRepository.GetGameFetch().UserStageEnhanceModels.FirstOrDefault(m =>
            {
                var mstStage = MstStageDataRepository.GetMstStage(m.MstStageId);
                if (mstStage == null) return false;
                var mstQuest = MstQuestDataRepository.GetMstQuestModels()
                    .FirstOrDefault(mst => mst.QuestType == QuestType.Enhance, MstQuestModel.Empty);
                return !mstQuest.IsEmpty();
            }, UserStageEnhanceModel.Empty);
            return userStageEnhanceModel;
        }

        public void RefreshRemainPvPSchedule()
        {
            var targetHours = MstConfigRepository.GetConfig(MstConfigKey.LocalNotificationPvpCountHours).Value.ToInt();
            var targetTime = GetTargetTime(targetHours);

            // ランクマッチは0時~12時非開催だが、通知飛ばす時間は20時なので見る必要性はなし
            var remainingCount = GameRepository.GetGameFetchOther().UserPvpStatusModel.RemainingChallengeCount;
            if (remainingCount.Value <= 0)
            {
                RemoveSchedule(LocalNotificationType.RemainPvP);
                return;
            }

            RefreshSchedule(
                LocalNotificationType.RemainPvP,
                "ランクマッチの挑戦回数が残ってるよ！",
                targetTime);
        }

        public void RefreshRemainAdventBattleCountSchedule()
        {
            // 降臨バトル
            var mstAdventBattleModel = MstAdventBattleDataRepository
                .GetMstAdventBattleModels()
                .FirstOrDefault(model =>
                    CalculateTimeCalculator.IsValidTime(TimeProvider.Now, model.StartDateTime.Value, model.EndDateTime.Value),
                        MstAdventBattleModel.Empty);
            if (mstAdventBattleModel.IsEmpty())
            {
                RemoveSchedule(LocalNotificationType.RemainAdventBattleCount);
                return;
            }

            var userAdventBattleModel = GameRepository
                .GetGameFetch()
                .UserAdventBattleModels
                .FirstOrDefault(model => model.MstAdventBattleId == mstAdventBattleModel.Id,
                    UserAdventBattleModel.Empty);

            // キャンペーン中の降臨バトルは、キャンペーンの効果値を加算する
            var challengeableCount = mstAdventBattleModel.ChallengeCount;
            var campaignModel = CampaignModelFactory.CreateCampaignModel(
                MasterDataId.Empty,
                CampaignTargetType.AdventBattle,
                CampaignTargetIdType.Quest,
                Difficulty.Normal,
                CampaignType.ChallengeCount);
            if (!campaignModel.IsEmpty())
            {
                challengeableCount += campaignModel.EffectValue;
            }
            challengeableCount -= userAdventBattleModel.ResetChallengeCount;

            if (challengeableCount.Value <= 0)
            {
                RemoveSchedule(LocalNotificationType.RemainAdventBattleCount);
                return;
            }

            var targetHours = MstConfigRepository.GetConfig(MstConfigKey.LocalNotificationEventOrAdventBattleCountHours)
                .Value
                .ToInt();
            var targetTime = GetTargetTime(targetHours);
            // 指定時間が開催時間外の場合は通知しない
            if (!CalculateTimeCalculator.IsValidTime(
                    targetTime,
                    mstAdventBattleModel.StartDateTime.Value,
                    mstAdventBattleModel.EndDateTime.Value))
            {
                RemoveSchedule(LocalNotificationType.RemainAdventBattleCount);
                return;
            }

            RefreshSchedule(
                LocalNotificationType.RemainAdventBattleCount,
                "降臨バトルに挑戦できるよ！\nクリア報酬でコインや強化素材などを手に入れよう♪",
                targetTime);
        }

        public void RefreshRemainAdGachaSchedule()
        {
            var gachaList = OprGachaRepository.GetOprGachaModelsByDataTime(TimeProvider.Now);
            var isExistDrawableAdGacha = gachaList.Any(gacha =>
            {
                var userGachaModel = GameRepository.GetGameFetchOther()
                    .UserGachaModels
                    .FirstOrDefault(mode=> mode.OprGachaId == gacha.Id) ?? UserGachaModel.CreateById(gacha.Id);

                return GachaEvaluator.IsAdGachaDrawable(gacha, userGachaModel).Value;

            });
            if (!isExistDrawableAdGacha)
            {
                RemoveSchedule(LocalNotificationType.RemainAdGacha);
                return;
            }

            var targetHours = MstConfigRepository.GetConfig(MstConfigKey.LocalNotificationAdGachaHours).Value.ToInt();
            var targetTime = GetTargetTime(targetHours);

            RefreshSchedule(
                LocalNotificationType.RemainAdGacha,
                "ガシャはもう引いた？動画を見て無料でガシャを引こう！",
                targetTime);
        }

        public void RefreshLoginSchedule()
        {
            // ログイン後24時間
            var targetHours = MstConfigRepository.GetConfig(MstConfigKey.LocalNotificationLoginAfterHoursOne).Value.ToInt();
            RefreshSchedule(
                LocalNotificationType.Login1,
                "お願い！ファントムの侵略からジャンブラバースを守って！",
                TimeProvider.Now.AddHours(targetHours));

            // ログイン後72時間
            targetHours = MstConfigRepository.GetConfig(MstConfigKey.LocalNotificationLoginAfterHoursTwo).Value.ToInt();
            RefreshSchedule(
                LocalNotificationType.Login2,
                "「少年ジャンプ＋」のキャラと力を合わせてジャンブラバースに平和を取り戻そう！",
                TimeProvider.Now.AddHours(targetHours));

            // ログイン後168時間
            targetHours = MstConfigRepository.GetConfig(MstConfigKey.LocalNotificationLoginAfterHoursThree).Value.ToInt();
            RefreshSchedule(
                LocalNotificationType.Login3,
                "「少年ジャンプ＋」のキャラクターたちがあなたを待ってるよ！\n今すぐ会いに行こう！",
                TimeProvider.Now.AddHours(targetHours));

            // ログイン後720時間
            targetHours = MstConfigRepository.GetConfig(MstConfigKey.LocalNotificationLoginAfterHoursFive).Value.ToInt();
            RefreshSchedule(
                LocalNotificationType.Login5,
                "あなたの好きな「少年ジャンプ＋」キャラが待ってるかも？\n今すぐログインしてチェックしてみよう♪",
                TimeProvider.Now.AddHours(targetHours));
        }

        public void RefreshBeginnerMissionSchedule()
        {
            var missionModel = MissionCacheRepository.GetMissionModel();
            if (missionModel == null)
            {
                RemoveSchedule(LocalNotificationType.BeginnerMission);
                return;
            }
            var dailyMissionModels = missionModel.UserMissionBeginnerModels;
            if (dailyMissionModels.All(m => m.IsCleared))
            {
                RemoveSchedule(LocalNotificationType.BeginnerMission);
                return;
            }

            var targetHours = MstConfigRepository.GetConfig(MstConfigKey.LocalNotificationBeginnerMissionAfterHours)
                .Value
                .ToInt();

            RefreshSchedule(
                LocalNotificationType.BeginnerMission,
                "ミッション達成でプリズム最大1,500個GETのチャンス！",
                TimeProvider.Now.AddHours(targetHours));
        }

        public void RefreshTutorialSchedule()
        {
            // 導入パートが終了している場合は通知しない
            if (!GameRepository.GetGameFetchOther().TutorialStatus.IsIntroduction())
            {
                RemoveSchedule(LocalNotificationType.Tutorial);
                return;
            }

            var targetHours = MstConfigRepository
                .GetConfig(MstConfigKey.LocalNotificationTutorialGachaAfterHours)
                .Value
                .ToInt();

            RefreshSchedule(
                LocalNotificationType.Tutorial,
                "無料でURキャラが1体手に入るチャンス！\nまずはチュートリアルをクリアしよう♪",
                TimeProvider.Now.AddHours(targetHours));
        }

#if GLOW_DEBUG
        public void DebugRefreshSchedule(LocalNotificationType type, string message, DateTimeOffset fireTime)
        {
            RefreshSchedule(type, message, fireTime);
        }
#endif //GLOW_DEBUG

        void RefreshSchedule(LocalNotificationType type, string message, DateTimeOffset fireTime)
        {
            RemoveSchedule(type);

            // プッシュ通知設定がオフなら設定しない
            if(!IsEnablePushNotification()) return;

            var identifier = LocalNotifier.AddSchedule("ジャンブラ", message, fireTime);
            if (identifier.IsEmpty()) return;

            _scheduleIdentifiers.Add(type, identifier);
            PreferenceRepository.LocalNotificationScheduledIdentifiers = _scheduleIdentifiers;
        }

        void RemoveSchedule(LocalNotificationType type)
        {
            _scheduleIdentifiers.TryGetValue(type, out var id);
            if (id != null && !string.IsNullOrEmpty(id.Value))
            {
                LocalNotifier.RemoveSchedule(id);
                _scheduleIdentifiers.Remove(type);
                PreferenceRepository.LocalNotificationScheduledIdentifiers = _scheduleIdentifiers;
            }
        }

        public void RemoveAllSchedules()
        {
            LocalNotifier.RemoveAllSchedule();
            _scheduleIdentifiers.Clear();
            PreferenceRepository.LocalNotificationScheduledIdentifiers = _scheduleIdentifiers;
        }

        public void RefreshAllSchedules()
        {
            RefreshIdleIncentiveSchedule();
            RefreshDailyMissionSchedule();
            RefreshRemainCoinQuestSchedule();
            RefreshRemainPvPSchedule();
            RefreshRemainAdventBattleCountSchedule();
            RefreshRemainAdGachaSchedule();
            RefreshLoginSchedule();
            RefreshBeginnerMissionSchedule();
            RefreshTutorialSchedule();
        }

        bool IsEnablePushNotification()
        {
            return !UserPropertyRepository.Get().IsPushOff;
        }
    }
}
