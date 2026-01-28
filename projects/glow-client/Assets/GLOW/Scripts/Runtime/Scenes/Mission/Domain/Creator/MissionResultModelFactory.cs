using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Translators;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Extensions;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.AdventBattleMission.Domain.Model;
using GLOW.Scenes.BeginnerMission.Domain.Model;
using GLOW.Scenes.EventMission.Domain.Model;
using GLOW.Scenes.Mission.Domain.Constant;
using GLOW.Scenes.Mission.Domain.Model.AchievementMission;
using GLOW.Scenes.Mission.Domain.Model.BonusPointMission;
using GLOW.Scenes.Mission.Domain.Model.DailyBonusMission;
using GLOW.Scenes.Mission.Domain.Model.DailyMission;
using GLOW.Scenes.Mission.Domain.Model.WeeklyMission;
using UnityEngine;

namespace GLOW.Scenes.Mission.Domain.Creator
{
    //純粋関数になっているけど、テスト記述の関係でinterface実装・Bindで利用している
    public class MissionResultModelFactory : IMissionResultModelFactory
    {
        MissionAchievementResultModel IMissionResultModelFactory.CreateMissionAchievementResultModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<UserMissionAchievementModel> userMissionAchievementModels)
        {
            var mstMissionAchievement = missionDataRepository.GetMstMissionAchievementModels();

            return new MissionAchievementResultModel(
                mstMissionAchievement
                    .GroupJoin(
                        userMissionAchievementModels,
                        mst => mst.Id,
                        user => user.MstMissionAchievementId,
                        (mst, users) => new { mst, user = users.FirstOrDefault() ?? UserMissionAchievementModel.Empty })
                    .Select(mstAndUser =>
                    {
                        return CreateMissionAchievementCellModel(
                            missionDataRepository, userMissionAchievementModels,
                            playerResourceModelFactory,
                            mstAndUser.mst, mstAndUser.user);
                    })
                    .Where(cell => !cell.IsEmpty())
                    .OrderBy(cell => cell.MissionStatus)
                    .ThenBy(cell => cell.SortOrder)
                    .ToList()
            );
        }

        MissionAchievementCellModel CreateMissionAchievementCellModel(
            IMstMissionDataRepository missionDataRepository,
            IReadOnlyList<UserMissionAchievementModel> userMissionAchievementModels,
            IPlayerResourceModelFactory playerResourceModelFactory,
            MstMissionAchievementModel mst,
            UserMissionAchievementModel user)
        {
            var dependencyIdAndUserModel = missionDataRepository
                .GetMstMissionAchievementDependencyModels()
                .Where(dependency => dependency.GroupId == mst.GroupId && dependency.UnlockOrder < mst.UnlockOrder)
                .Select(model => model.MstMissionAchievementId)
                .Join(userMissionAchievementModels,
                    dependencyId => dependencyId,
                    userModel => userModel.MstMissionAchievementId,
                    (dependencyId, userModel) => new { dependencyId, userModel });

            // Dependencyで設定されているミッションの場合、それを下回るUnlockOrderが設定されているミッションを全てクリアしていない場合はEmptyを返す(表示しない)
            var isAllClear = dependencyIdAndUserModel.All(dependencyId => dependencyId.userModel.IsCleared);
            if (!isAllClear)
            {
                return MissionAchievementCellModel.Empty;
            }

            return new MissionAchievementCellModel(
                mst.Id,
                MissionType.Achievement,
                MissionStatusTranslator.ToMissionStatus(user.IsCleared, user.IsReceivedReward),
                user.Progress,
                mst.CriterionValue,
                mst.CriterionCount,
                missionDataRepository
                    .GetMissionRewardModelList(mst.MstMissionRewardGroupId)
                    .Select(m => playerResourceModelFactory.Create(
                        m.ResourceType,
                        m.ResourceId,
                        m.ResourceAmount.ToPlayerResourceAmount()))
                    .ToList(),
                mst.MissionDescription,
                mst.SortOrder,
                mst.DestinationScene);
        }

        MissionDailyBonusResultModel IMissionResultModelFactory.CreateMissionDailyBonusResultModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<UserMissionDailyBonusModel> userMissionDailyBonusModels,
            UserLoginInfoModel userLoginInfoModel,
            IReadOnlyList<MissionReceivedDailyBonusModel> missionReceivedDailyBonusModel)
        {
            var mstMissionDailyBonus = missionDataRepository.GetMstMissionDailyBonusModels();
            var missionDailyBonusCellModels = mstMissionDailyBonus
                .Where(dailyBonus => !IsTotalDailyBonusMission(dailyBonus.MissionDailyBonusType))
                .GroupJoin(userMissionDailyBonusModels,
                    mst => mst.Id,
                    user => user.MstMissionDailyBonusId,
                    (mst, users) => new { mst, user = users.FirstOrDefault() ?? UserMissionDailyBonusModel.Empty })
                .Select(mstAndUser =>
                {
                    // ログインボーナスに一致するものが存在しているか？(存在している場合は受け取り前の状態とする)
                    var isContainLoginBonus = missionReceivedDailyBonusModel.Find(bonus =>
                        !IsTotalDailyBonusMission(bonus.MissionType) &&
                        bonus.LoginDayCount == mstAndUser.mst.LoginDayCount) != null;

                    var resourceModels = missionDataRepository
                        .GetMissionRewardModelList(mstAndUser.mst.MstMissionRewardGroupId)
                        .Select(r =>
                            playerResourceModelFactory.Create(
                                r.ResourceType,
                                r.ResourceId,
                                r.ResourceAmount.ToPlayerResourceAmount()))
                        .ToList();

                    return new MissionDailyBonusCellModel(
                        mstAndUser.mst.Id,
                        MissionType.DailyBonus,
                        MissionStatusTranslator.ToMissionStatus(
                            mstAndUser.user.IsCleared,
                            new MissionReceivedFlag(
                                mstAndUser.user.IsReceivedReward &&
                                !isContainLoginBonus)),
                        mstAndUser.mst.LoginDayCount,
                        resourceModels
                    );
                })
                .ToList();

            return new MissionDailyBonusResultModel(missionDailyBonusCellModels);
        }

        MissionDailyResultModel IMissionResultModelFactory.CreateMissionDailyResultModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<UserMissionDailyModel> userMissionDailyModels,
            IReadOnlyList<UserMissionBonusPointModel> userMissionBonusPointModels)
        {
            var mstMissionDaily = missionDataRepository.GetMstMissionDailyModels();
            var bonusPointResultModel = CreateDailyMissionBonusPointResultModel(
                missionDataRepository,
                playerResourceModelFactory,
                mstMissionDaily,
                userMissionBonusPointModels.First(model => model.MissionType == MissionType.Daily));

            var missionDailyCellModels = mstMissionDaily
                .Where(daily => !IsBonusPointMission(daily.CriterionType))
                .GroupJoin(userMissionDailyModels,
                    mst => mst.Id,
                    user => user.MstMissionDailyId,
                    (mst, users) => new { mst, user = users.FirstOrDefault() ?? UserMissionDailyModel.Empty })
                .Select(mstAndUser =>
                {
                    return new MissionDailyCellModel(
                        mstAndUser.mst.Id,
                        MissionType.Daily,
                        MissionStatusTranslator.ToMissionStatus(
                            mstAndUser.user.IsCleared,
                            mstAndUser.user.IsReceivedReward,
                            bonusPointResultModel.IsBonusPointMissionAllReceived()),
                        mstAndUser.user.Progress,
                        mstAndUser.mst.CriterionCount,
                        mstAndUser.mst.BonusPoint,
                        mstAndUser.mst.MissionDescription,
                        mstAndUser.mst.SortOrder,
                        mstAndUser.mst.DestinationScene);
                })
                .OrderBy(cell => cell.MissionStatus)
                .ThenBy(cell => cell.SortOrder)
                .ToList();

            return new MissionDailyResultModel(bonusPointResultModel, missionDailyCellModels);
        }

        MissionWeeklyResultModel IMissionResultModelFactory.CreateMissionWeeklyResultModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<UserMissionWeeklyModel> userMissionWeeklyModels,
            IReadOnlyList<UserMissionBonusPointModel> userMissionBonusPointModels)
        {
            var mstMissionWeekly = missionDataRepository.GetMstMissionWeeklyModels();
            var bonusPointResultModel = CreateWeeklyMissionBonusPointResultModel(
                missionDataRepository,
                playerResourceModelFactory,
                mstMissionWeekly,
                userMissionBonusPointModels.First(model => model.MissionType == MissionType.Weekly));

            var missionWeeklyCellModels = mstMissionWeekly
                .Where(weekly => !IsBonusPointMission(weekly.CriterionType))
                .GroupJoin(userMissionWeeklyModels,
                    mst => mst.Id,
                    user => user.MstMissionWeeklyId,
                    (mst, users) => new { mst, user = users.FirstOrDefault() ?? UserMissionWeeklyModel.Empty })
                .Select(mstAndUser =>
                {
                    return new MissionWeeklyCellModel(
                        mstAndUser.mst.Id,
                        MissionType.Weekly,
                        MissionStatusTranslator.ToMissionStatus(
                            mstAndUser.user.IsCleared,
                            mstAndUser.user.IsReceivedReward,
                            bonusPointResultModel.IsBonusPointMissionAllReceived()),
                        mstAndUser.user.Progress,
                        mstAndUser.mst.CriterionCount,
                        mstAndUser.mst.BonusPoint,
                        mstAndUser.mst.MissionDescription,
                        mstAndUser.mst.SortOrder,
                        mstAndUser.mst.DestinationScene);
                }).OrderBy(cell => cell.MissionStatus).ThenBy(cell => cell.SortOrder).ToList();

            return new MissionWeeklyResultModel(bonusPointResultModel, missionWeeklyCellModels);
        }

        MissionBeginnerResultModel IMissionResultModelFactory.CreateMissionBeginnerResultModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<UserMissionBeginnerModel> userMissionBeginnerModels,
            IReadOnlyList<UserMissionBonusPointModel> userMissionBonusPointModels)
        {
            var mstMissionBeginner = missionDataRepository.GetMstMissionBeginnerModels();
            var bonusPointResultModel = CreateBeginnerMissionBonusPointResultModel(
                missionDataRepository,
                playerResourceModelFactory,
                mstMissionBeginner,
                userMissionBonusPointModels.First(model => model.MissionType == MissionType.Beginner));

            var missionBeginnerCellModels = mstMissionBeginner
                .Where(beginner => !IsBonusPointMission(beginner.CriterionType))
                .GroupJoin(userMissionBeginnerModels,
                    mst => mst.Id,
                    user => user.MstMissionBeginnerId,
                    (mst, users) => new { mst, user = users.FirstOrDefault() ?? UserMissionBeginnerModel.Empty })
                .Select(mstAndUser =>
                {
                    return new MissionBeginnerCellModel(
                        mstAndUser.mst.Id,
                        MissionType.Beginner,
                        MissionStatusTranslator.ToMissionStatus(mstAndUser.user.IsCleared, mstAndUser.user.IsReceivedReward),
                        mstAndUser.user.Progress,
                        mstAndUser.mst.UnlockDay,
                        mstAndUser.mst.CriterionValue,
                        mstAndUser.mst.CriterionCount,
                        mstAndUser.mst.BonusPoint,
                        missionDataRepository
                            .GetMissionRewardModelList(mstAndUser.mst.MstMissionRewardGroupId)
                            .Select(r =>
                                playerResourceModelFactory.Create(
                                    r.ResourceType,
                                    r.ResourceId,
                                    r.ResourceAmount.ToPlayerResourceAmount()))
                            .ToList(),
                        mstAndUser.mst.MissionDescription,
                        mstAndUser.mst.SortOrder,
                        mstAndUser.mst.DestinationScene);
                })
                .OrderBy(cell => cell.MissionStatus)
                .ThenBy(cell => cell.SortOrder)
                .ToList();

            return new MissionBeginnerResultModel(bonusPointResultModel, missionBeginnerCellModels);
        }

        MissionBonusPointResultModel CreateDailyMissionBonusPointResultModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<MstMissionDailyModel> mstMissionDailyModels,
            UserMissionBonusPointModel userMissionBonusPointModel)
        {
            return new MissionBonusPointResultModel(
                userMissionBonusPointModel.MissionType,
                userMissionBonusPointModel.Point,
                mstMissionDailyModels
                    .Where(daily => IsBonusPointMission(daily.CriterionType))
                    .Select(mst =>
                    {
                        var isCleared = userMissionBonusPointModel.Point >= mst.CriterionCount;
                        var isReceivedReward =
                            userMissionBonusPointModel.ReceivedRewardPoints.Contains(mst.CriterionCount.ToBonusPoint());
                        return new MissionBonusPointCellModel(
                            mst.Id,
                            MissionType.Daily,
                            MissionStatusTranslator.ToMissionStatus(
                                new MissionClearFrag(isCleared),
                                new MissionReceivedFlag(isReceivedReward)),
                            mst.CriterionCount,
                            missionDataRepository.GetMissionRewardModelList(mst.MstMissionRewardGroupId)
                                .Select(r =>
                                    playerResourceModelFactory.Create(
                                        r.ResourceType,
                                        r.ResourceId,
                                        r.ResourceAmount.ToPlayerResourceAmount()))
                                .ToList()
                        );
                    }).ToList());
        }

        MissionBonusPointResultModel CreateWeeklyMissionBonusPointResultModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<MstMissionWeeklyModel> mstMissionWeeklyModels,
            UserMissionBonusPointModel userMissionBonusPointModel)
        {
            return new MissionBonusPointResultModel(
                userMissionBonusPointModel.MissionType,
                userMissionBonusPointModel.Point,
                mstMissionWeeklyModels
                    .Where(daily => IsBonusPointMission(daily.CriterionType))
                    .Select(mst =>
                    {
                        var isCleared = userMissionBonusPointModel.Point >= mst.CriterionCount;
                        var isReceivedReward =
                            userMissionBonusPointModel.ReceivedRewardPoints.Contains(mst.CriterionCount.ToBonusPoint());
                        return new MissionBonusPointCellModel(
                            mst.Id,
                            MissionType.Weekly,
                            MissionStatusTranslator.ToMissionStatus(
                                new MissionClearFrag(isCleared),
                                new MissionReceivedFlag(isReceivedReward)),
                            mst.CriterionCount,
                            missionDataRepository
                                .GetMissionRewardModelList(mst.MstMissionRewardGroupId)
                                .Select(r =>
                                    playerResourceModelFactory.Create(
                                        r.ResourceType,
                                        r.ResourceId,
                                        r.ResourceAmount.ToPlayerResourceAmount()))
                                .ToList()
                        );
                    }).ToList());
        }

        MissionBonusPointResultModel CreateBeginnerMissionBonusPointResultModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<MstMissionBeginnerModel> mstMissionBeginnerModels,
            UserMissionBonusPointModel userMissionBonusPointModel)
        {
            return new MissionBonusPointResultModel(
                userMissionBonusPointModel.MissionType,
                userMissionBonusPointModel.Point,
                mstMissionBeginnerModels
                    .Where(daily => IsBonusPointMission(daily.CriterionType))
                    .Select(mst =>
                    {
                        var isCleared = userMissionBonusPointModel.Point >= mst.CriterionCount;
                        var isReceivedReward =
                            userMissionBonusPointModel.ReceivedRewardPoints.Contains(mst.CriterionCount.ToBonusPoint());
                        return new MissionBonusPointCellModel(
                            mst.Id,
                            MissionType.Beginner,
                            MissionStatusTranslator.ToMissionStatus(
                                new MissionClearFrag(isCleared),
                                new MissionReceivedFlag(isReceivedReward)),
                            mst.CriterionCount,
                            missionDataRepository
                                .GetMissionRewardModelList(mst.MstMissionRewardGroupId)
                                .Select(r =>
                                    playerResourceModelFactory.Create(
                                        r.ResourceType,
                                        r.ResourceId,
                                        r.ResourceAmount.ToPlayerResourceAmount()))
                                .ToList()
                        );
                    }).ToList());
        }

        EventMissionAchievementResultModel IMissionResultModelFactory.CreateEventMissionAchievementResultModel(
            IReadOnlyList<MstEventModel> mstEventModels,
            ITimeProvider timeProvider,
            IMstMissionEventDataRepository missionDataRepository,
            IMstMissionRewardDataRepository mstRewardDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<UserMissionEventModel> userMissionEventAchievementModels)
        {
            var mstMissionEventAchievement = missionDataRepository.GetMstMissionEventModels();

            var list = mstMissionEventAchievement
                .Join(mstEventModels,
                    mst => mst.MstEventId,
                    mstEvent => mstEvent.Id,
                    (mstMission, mstEvent) => new { mstMission, mstEvent })
                .Where(mst => mst.mstEvent.StartAt <= timeProvider.Now && timeProvider.Now <= mst.mstEvent.EndAt)
                .GroupJoin(userMissionEventAchievementModels,
                    mst => mst.mstMission.Id,
                    user => user.MstMissionEventId,
                    (mst, users) =>
                        new { mst, user = users.FirstOrDefault() ?? UserMissionEventModel.Empty })
                .Select(mstAndUser => CreateEventMissionCellModel(
                    missionDataRepository,
                    mstRewardDataRepository,
                    playerResourceModelFactory,
                    userMissionEventAchievementModels,
                    mstAndUser.mst.mstMission,
                    mstAndUser.user))
                .Where(cell => !cell.IsEmpty())
                .OrderBy(cell => cell.MissionStatus)
                .ThenBy(cell => cell.SortOrder)
                .ToList();

            var openingMstEventModels = mstEventModels
                .Where(m => CalculateTimeCalculator.IsValidTime(timeProvider.Now,m.StartAt, m.EndAt)).ToList();

            return new EventMissionAchievementResultModel(list, openingMstEventModels);
        }

        EventMissionCellModel CreateEventMissionCellModel(
            IMstMissionEventDataRepository missionDataRepository,
            IMstMissionRewardDataRepository mstRewardDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<UserMissionEventModel> userMissionEventAchievementModels,
            MstMissionEventModel mst,
            UserMissionEventModel user)
        {
            var dependencyIdList = missionDataRepository
                .GetMstMissionEventDependencyModels()
                .Where(dependency => dependency.GroupId == mst.GroupId && dependency.UnlockOrder < mst.UnlockOrder)
                .Select(model => model.MstMissionEventId)
                .Join(userMissionEventAchievementModels,
                    dependency => dependency,
                    userModel => userModel.MstMissionEventId,
                    (dependency, userModel) => new { dependency, userModel });

            // Dependencyで設定されているミッションの場合、それを下回るUnlockOrderが設定されているミッションを全てクリアしていない場合はEmptyを返す(表示しない)
            var isAllClear = dependencyIdList.All(dependencyIdList => dependencyIdList.userModel.IsCleared);
            if (!isAllClear)
            {
                return EventMissionCellModel.Empty;
            }

            return new EventMissionCellModel(
                mst.Id,
                mst.MstEventId,
                MissionType.Event,
                MissionStatusTranslator.ToMissionStatus(user.IsCleared, user.IsReceivedReward),
                user.Progress,
                mst.CriterionCount,
                mstRewardDataRepository
                    .GetMissionRewardModelList(mst.MstMissionRewardGroupId)
                    .Select(r =>
                        playerResourceModelFactory.Create(
                            r.ResourceType,
                            r.ResourceId,
                            r.ResourceAmount.ToPlayerResourceAmount()))
                    .ToList(),
                mst.MissionDescription,
                mst.SortOrder,
                mst.DestinationScene);
        }

        // デザイン含めMstEventId 1種類での表示想定になっている
        EventMissionDailyBonusResultModel IMissionResultModelFactory.CreateEventMissionDailyBonusResultModel(
            MstEventModel mstEventModel,
            IMstMissionEventDataRepository missionDataRepository,
            IMstMissionRewardDataRepository mstRewardDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            MasterDataId mstEventScheduleId,
            IReadOnlyList<MissionEventDailyBonusRewardModel> eventDailyBonusRewardModels,
            IReadOnlyList<UserMissionEventDailyBonusProgressModel> userMissionEventDailyBonusProgressModels)
        {
            var userMissionEventDailyBonusProgressModel = userMissionEventDailyBonusProgressModels
                .FirstOrDefault(
                    model => model.MstMissionEventDailyBonusScheduleId == mstEventScheduleId,
                    UserMissionEventDailyBonusProgressModel.Empty);
            if (userMissionEventDailyBonusProgressModel.IsEmpty())
            {
                return EventMissionDailyBonusResultModel.Empty;
            }

            var mstMissionEventDailyBonus = missionDataRepository
                .GetMstMissionEventDailyBonusModels(userMissionEventDailyBonusProgressModel.MstMissionEventDailyBonusScheduleId);
            var eventMissionDailyBonusCellModels = mstMissionEventDailyBonus
                .Select(mst => CreateEventMissionDailyBonusCellModel(
                    mstRewardDataRepository,
                    playerResourceModelFactory,
                    eventDailyBonusRewardModels,
                    userMissionEventDailyBonusProgressModel,
                    mst))
                .ToList();

            // 進捗が達成している日数がログボのセル数を超えている場合はEmptyを返す
            if (userMissionEventDailyBonusProgressModel.ProgressLoginDayCount > eventMissionDailyBonusCellModels.Count)
            {
                return EventMissionDailyBonusResultModel.Empty;
            }

            var surplus = eventMissionDailyBonusCellModels.Count % 4; // 8 -> 0, 7 -> 1, 6 -> 2, 5 -> 3
            var addEmptyCellCount = surplus == 0 ? 0 : 4 - surplus;
            for (var i = 0; i < addEmptyCellCount; i++)
            {
                // 4の倍数になるように空セルを追加
                eventMissionDailyBonusCellModels.Add(EventMissionDailyBonusCellModel.Empty);
            }

            return new EventMissionDailyBonusResultModel(
                userMissionEventDailyBonusProgressModel.ProgressLoginDayCount,
                eventMissionDailyBonusCellModels,
                CreateCommonReceiveResourceModel(eventDailyBonusRewardModels, playerResourceModelFactory));
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveResourceModel(
            IReadOnlyList<MissionEventDailyBonusRewardModel> eventDailyBonusRewardModels,
            IPlayerResourceModelFactory playerResourceModelFactory)
        {
            return eventDailyBonusRewardModels
                .Select(m => new CommonReceiveResourceModel(
                    m.RewardModel.UnreceivedRewardReasonType,
                    playerResourceModelFactory.Create(
                        m.RewardModel.ResourceType,
                        m.RewardModel.ResourceId,
                        m.RewardModel.Amount),
                    playerResourceModelFactory.Create(m.RewardModel.PreConversionResource)))
                .ToList();
        }

        EventMissionDailyBonusCellModel CreateEventMissionDailyBonusCellModel(
            IMstMissionRewardDataRepository missionRewardDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<MissionEventDailyBonusRewardModel> eventDailyBonusRewardModels,
            UserMissionEventDailyBonusProgressModel userMissionEventDailyBonusProgressModel,
            MstMissionEventDailyBonusModel mst
        )
        {
            var status = DailyBonusReceiveStatus.CannotReceive;
            if (eventDailyBonusRewardModels.Any(model => model.LoginDayCount == mst.LoginDayCount))
            {
                // 受け取っている報酬に含まれている場合
                status = DailyBonusReceiveStatus.Receiving;
            }
            else if (userMissionEventDailyBonusProgressModel.ProgressLoginDayCount >= mst.LoginDayCount)
            {
                // 進捗が達成している場合
                status = DailyBonusReceiveStatus.Received;
            }

            var rewardPlayerResourceModel = CreateCommonReceiveResourceModel(
                missionRewardDataRepository,
                playerResourceModelFactory,
                mst);

            return new EventMissionDailyBonusCellModel(
                mst.Id,
                status,
                mst.LoginDayCount,
                rewardPlayerResourceModel,
                mst.SortOrder);
        }

        CommonReceiveResourceModel CreateCommonReceiveResourceModel(
            IMstMissionRewardDataRepository missionRewardDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            MstMissionEventDailyBonusModel mst)
        {
            var rewardModels =
                missionRewardDataRepository.GetMissionRewardModelList(mst.MstMissionRewardGroupId);
            if (rewardModels.Count <= 0)
            {
                Debug.LogError($"1つ以上MstMissionRewardを設定してください...MstMissionRewardGroupId: {mst.MstMissionRewardGroupId}");
                return CommonReceiveResourceModel.Empty;
            }

            return rewardModels
                .Select(r =>
                    new CommonReceiveResourceModel(
                        //TODO: サーバーからRewardDataで送って貰う必要があるかも
                        UnreceivedRewardReasonType.None,
                        playerResourceModelFactory.Create(
                            r.ResourceType,
                            r.ResourceId,
                            r.ResourceAmount.ToPlayerResourceAmount()),
                        PlayerResourceModel.Empty
                    ))
                .First();
        }

        AdventBattleMissionFetchResultModel IMissionResultModelFactory.CreateAdventBattleMissionResultModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<UserMissionEventModel> userMissionEventModels,
            IReadOnlyList<UserMissionLimitedTermModel> userMissionLimitedTermModels,
            ITimeProvider timeProvider,
            AdventBattleEndDateTime endDateTime)
        {
            var mstMissionEventAdventBattle = missionDataRepository.GetMstMissionEventModels()
                .Where(model => model.EventCategory == EventCategory.AdventBattle);
            var eventList = mstMissionEventAdventBattle
                .GroupJoin(userMissionEventModels,
                    mst => mst.Id,
                    user => user.MstMissionEventId,
                    (mst, users) => new { mst, user = users.FirstOrDefault() ?? UserMissionEventModel.Empty })
                .Select(mstAndUser => CreateAdventBattleMissionCellModel(missionDataRepository,
                    playerResourceModelFactory,
                    timeProvider, endDateTime, userMissionEventModels, mstAndUser.mst, mstAndUser.user))
                .Where(cell => !cell.IsEmpty());

            var mstMissionLimitedTerm = missionDataRepository.GetMstMissionLimitedTermModels()
                .Where(model => CalculateTimeCalculator.IsValidTime(
                    timeProvider.Now,
                    model.StartDate.Value,
                    model.EndDate.Value))
                .Where(model => model.MissionCategory == MissionCategory.AdventBattle);
            var limitedTermList = mstMissionLimitedTerm
                .GroupJoin(userMissionLimitedTermModels,
                    mst => mst.Id,
                    user => user.MstMissionLimitedTermId,
                    (mst, users) => new { mst, user = users.FirstOrDefault() ?? UserMissionLimitedTermModel.Empty })
                .Select(mstAndUser => CreateAdventBattleMissionCellModel(missionDataRepository,
                    playerResourceModelFactory,
                    timeProvider, userMissionLimitedTermModels, mstAndUser.mst, mstAndUser.user))
                .Where(cell => !cell.IsEmpty());

            var allList = eventList.Concat(limitedTermList)
                .OrderBy(cell => cell.MissionStatus)
                .ThenBy(cell => cell.SortOrder)
                .ThenByDescending(cell => cell.MissionType)
                .ToList();

            return new AdventBattleMissionFetchResultModel(allList);
        }

        AdventBattleMissionCellModel CreateAdventBattleMissionCellModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            ITimeProvider timeProvider,
            AdventBattleEndDateTime endDateTime,
            IReadOnlyList<UserMissionEventModel> userMissionEventAchievementModels,
            MstMissionEventModel mst,
            UserMissionEventModel user)
        {
            // 期間的に無効なミッションの場合はEmptyを返す(表示しない)
            var isInvalid = endDateTime.Value < timeProvider.Now;
            if (isInvalid)
            {
                return AdventBattleMissionCellModel.Empty;
            }

            var dependencyIdList = missionDataRepository
                .GetMstMissionEventDependencyModels()
                .Where(dependency => dependency.GroupId == mst.GroupId && dependency.UnlockOrder < mst.UnlockOrder)
                .Select(model => model.MstMissionEventId)
                .Join(userMissionEventAchievementModels,
                    dependency => dependency,
                    userModel => userModel.MstMissionEventId,
                    (dependency, userModel) => new { dependency, userModel });

            // Dependencyで設定されているミッションの場合、それを下回るUnlockOrderが設定されているミッションを全てクリアしていない場合はEmptyを返す(表示しない)
            var isNotAllClear = dependencyIdList.Any(id => !id.userModel.IsCleared);
            if (isNotAllClear)
            {
                return AdventBattleMissionCellModel.Empty;
            }

            var rewardModels = missionDataRepository
                .GetMissionRewardModelList(mst.MstMissionRewardGroupId)
                .Select(model => playerResourceModelFactory.Create(
                    model.ResourceType,
                    model.ResourceId,
                    model.ResourceAmount.ToPlayerResourceAmount()))
                .ToList();

            return new AdventBattleMissionCellModel(
                mst.Id,
                MissionType.Event,
                MissionCategory.AdventBattle,
                MissionStatusTranslator.ToMissionStatus(user.IsCleared, user.IsReceivedReward),
                user.Progress,
                mst.CriterionCount,
                rewardModels,
                mst.MissionDescription,
                mst.SortOrder,
                mst.DestinationScene,
                CalculateTimeCalculator.GetRemainingTime(timeProvider.Now, endDateTime.Value));
        }

        AdventBattleMissionCellModel CreateAdventBattleMissionCellModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            ITimeProvider timeProvider,
            IReadOnlyList<UserMissionLimitedTermModel> userMissionLimitedTermModels,
            MstMissionLimitedTermModel mst,
            UserMissionLimitedTermModel user)
        {
            // 期間的に無効なミッションの場合はEmptyを返す(表示しない)
            var isInvalid = mst.EndDate.Value < timeProvider.Now;
            if (isInvalid)
            {
                return AdventBattleMissionCellModel.Empty;
            }

            var dependencyIdList = missionDataRepository
                .GetMstMissionLimitedTermDependencyModels()
                .Where(dependency => dependency.GroupId == mst.GroupId && dependency.UnlockOrder < mst.UnlockOrder)
                .Select(model => model.MstMissionLimitedTermId)
                .Join(userMissionLimitedTermModels,
                    dependency => dependency,
                    userModel => userModel.MstMissionLimitedTermId,
                    (dependency, userModel) => new { dependency, userModel });

            // Dependencyで設定されているミッションの場合、それを下回るUnlockOrderが設定されているミッションを全てクリアしていない場合はEmptyを返す(表示しない)
            var isNotAllClear = dependencyIdList.Any(id => !id.userModel.IsCleared);
            if (isNotAllClear)
            {
                return AdventBattleMissionCellModel.Empty;
            }

            var rewardModels = missionDataRepository
                .GetMissionRewardModelList(mst.MstMissionRewardGroupId)
                .Select(model => playerResourceModelFactory.Create(
                    model.ResourceType,
                    model.ResourceId,
                    model.ResourceAmount.ToPlayerResourceAmount()))
                .ToList();

            return new AdventBattleMissionCellModel(
                mst.Id,
                MissionType.LimitedTerm,
                MissionCategory.AdventBattle,
                MissionStatusTranslator.ToMissionStatus(user.IsCleared, user.IsReceivedReward),
                user.Progress,
                mst.CriterionCount,
                rewardModels,
                mst.MissionDescription,
                mst.SortOrder,
                mst.DestinationScene,
                CalculateTimeCalculator.GetRemainingTime(timeProvider.Now, mst.EndDate.Value));
        }

        bool IsBonusPointMission(MissionCriterionType criterionType)
        {
            return criterionType == MissionConst.BonusPointCriterionType;
        }

        bool IsTotalDailyBonusMission(MissionDailyBonusType dailyBonusType)
        {
            return dailyBonusType == MissionConst.TotalDailyBonusType;
        }
    }
}
