using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Extensions;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.BeginnerMission.Domain.Model;
using GLOW.Scenes.Mission.Domain.Calculator;
using GLOW.Scenes.Mission.Domain.Creator;
using GLOW.Scenes.Mission.Domain.Definition.Service;
using GLOW.Scenes.Mission.Domain.Extension;
using GLOW.Scenes.Mission.Domain.Model;
using GLOW.Scenes.Mission.Domain.Model.AchievementMission;
using GLOW.Scenes.Mission.Domain.Model.DailyBonusMission;
using GLOW.Scenes.Mission.Domain.Model.DailyMission;
using GLOW.Scenes.Mission.Domain.Model.WeeklyMission;
using Zenject;

namespace GLOW.Scenes.Mission.Domain.UseCase
{
    public class BulkReceiveMissionRewardUseCase
    {
        [Inject] IMissionService MissionService { get; }
        [Inject] IMstMissionDataRepository MissionDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMissionCacheRepository MissionCacheRepository { get; }
        [Inject] IUserLevelUpCacheRepository UserLevelUpCacheRepository { get; }
        [Inject] IMissionResultModelFactory MissionResultModelFactory { get; }

        public async UniTask<BulkReceiveMissionRewardUseCaseModel>
            BulkReceiveMissionReward(CancellationToken cancellationToken, MissionType missionType)
        {
            var missionIds = GetReceivableMissionIds(missionType);

            var missionModel = MissionCacheRepository.GetMissionModel();
            if (missionType == MissionType.Beginner)
            {
                // 初心者ミッションは、受け取り時にロックされているものを除外する
                missionIds = RemoveLockedBeginnerMissionIds(
                    missionIds,
                    missionModel.BeginnerMissionDaysFromStart);
            }

            var receiveMissionRewardModel = await MissionService.BulkReceiveReward(
                cancellationToken,
                missionType,
                missionIds);

            // 受け取ったミッションのId
            var receivedMissionIds = receiveMissionRewardModel.MissionReceiveRewardModels
                .Where(data => data.UnreceivedRewardReason == UnreceivedRewardReasonType.None)
                .Select(data => data.MstMissionId)
                .ToList();

            foreach (var receivedMissionId in receivedMissionIds)
            {
                //CacheRepositoryの副作用
                MissionCacheRepository.UpdateMissionStatus(missionType, receivedMissionId, MissionStatus.Received);
            }

            if (missionType.ExistBonusPointMission())
            {
                var missionIdSet = missionIds.ToHashSet();
                // CacheRepositoryの副作用
                SaveReceivedBonusPointMission(
                    missionType,
                    receiveMissionRewardModel,
                    missionIdSet);
            }

            var userMissionAchievement = missionModel.UserMissionAchievementModels;
            var userMissionBonusPoint = missionModel.UserMissionBonusPointModels;
            var userMissionDaily = missionModel.UserMissionDailyModels;
            var userMissionWeekly = missionModel.UserMissionWeeklyModels;
            var userMissionDailyBonus = missionModel.UserMissionDailyBonusModels;

            var gameFetchOtherModel = GameRepository.GetGameFetchOther();

            var missionAchievementResultModel = MissionResultModelFactory.CreateMissionAchievementResultModel(
                MissionDataRepository,
                PlayerResourceModelFactory,
                userMissionAchievement);

            var missionDailyBonusResultModel = MissionResultModelFactory.CreateMissionDailyBonusResultModel(
                MissionDataRepository,
                PlayerResourceModelFactory,
                userMissionDailyBonus,
                gameFetchOtherModel.UserLoginInfoModel,
                gameFetchOtherModel.MissionReceivedDailyBonusModel);

            var missionDailyResultModel = MissionResultModelFactory.CreateMissionDailyResultModel(
                MissionDataRepository,
                PlayerResourceModelFactory,
                userMissionDaily,
                userMissionBonusPoint);

            var missionWeeklyResultModel = MissionResultModelFactory.CreateMissionWeeklyResultModel(
                MissionDataRepository,
                PlayerResourceModelFactory,
                userMissionWeekly,
                userMissionBonusPoint);

            var missionBeginnerResultModel = MissionResultModelFactory.CreateMissionBeginnerResultModel(
                MissionDataRepository,
                PlayerResourceModelFactory,
                missionModel.UserMissionBeginnerModels,
                userMissionBonusPoint);

            //副作用
            ApplyUpdateFetchModel(
                missionAchievementResultModel,
                missionDailyBonusResultModel,
                missionDailyResultModel,
                missionWeeklyResultModel,
                missionBeginnerResultModel,
                receiveMissionRewardModel,
                missionModel.BeginnerMissionDaysFromStart);


            var prevUserParameterModel = GameRepository.GetGameFetch().UserParameterModel;
            // 経験値を受け取れる関係でレベルアップする可能性がある
            UserLevelUpCacheRepository.Save(
                receiveMissionRewardModel.UserLevelUpModel,
                prevUserParameterModel.Level,
                prevUserParameterModel.Exp);

            var missionFetchResultModel = CreateMissionFetchResultModel(
                missionAchievementResultModel,
                missionDailyBonusResultModel,
                missionDailyResultModel,
                missionWeeklyResultModel,
                missionBeginnerResultModel);

            var commonReceiveModels =
                CreateCommonReceiveModelFromMissionBonusPoint(missionType, missionIds)
                    .Concat(CreateCommonReceiveModelFromMissionRewardModel(
                        missionIds,
                        receiveMissionRewardModel.MissionRewardModels))
                    .ToList();

            return new BulkReceiveMissionRewardUseCaseModel(
                commonReceiveModels,
                missionFetchResultModel
            );
        }

        void ApplyUpdateFetchModel(
            MissionAchievementResultModel missionAchievementResultModel,
            MissionDailyBonusResultModel missionDailyBonusResultModel,
            MissionDailyResultModel missionDailyResultModel,
            MissionWeeklyResultModel missionWeeklyResultModel,
            MissionBeginnerResultModel missionBeginnerResultModel,
            MissionBulkReceiveRewardResultModel receiveMissionRewardModel,
            BeginnerMissionDaysFromStart beginnerMissionDaysFromStart)
        {
            var calculatedReceivableMissionCount = ReceivableMissionCountCalculator.GetReceivableMissionCount(
                missionAchievementResultModel,
                missionDailyBonusResultModel,
                missionDailyResultModel,
                missionWeeklyResultModel);

            var calculatedReceivableMissionBeginnerCount = ReceivableMissionCountCalculator.GetReceivableMissionBeginnerCount(
                missionBeginnerResultModel,
                beginnerMissionDaysFromStart);

            var updatedFetchModel = UpdateFetchModel(
                receiveMissionRewardModel.UserParameterModel,
                calculatedReceivableMissionCount,
                calculatedReceivableMissionBeginnerCount);

            var updatedFetchOtherModel = UpdateFetchOtherModel(
                receiveMissionRewardModel.ConditionPackModels,
                receiveMissionRewardModel.UserItemModels,
                receiveMissionRewardModel.UserUnitModels);

            GameManagement.SaveGameUpdateAndFetch(updatedFetchModel, updatedFetchOtherModel);
        }


        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveModelFromMissionRewardModel(
            IReadOnlyList<MasterDataId> missionIds,
            IReadOnlyList<MissionRewardModel> missionRewardModels)
        {
            var missionIdSet = missionIds.ToHashSet();
            // 獲得したポイントのミッションと同じミッションIdで獲得した報酬を取得(ボーナスポイント報酬など、ほかが混ざってくることがある)
            var receivedMissionRewards = missionRewardModels
                .Where(model => missionIdSet.Contains(model.MissionId))
                .ToList();

            return receivedMissionRewards
                .Select(r =>
                    new CommonReceiveResourceModel(
                        r.RewardModel.UnreceivedRewardReasonType,
                        PlayerResourceModelFactory.Create(
                            r.RewardModel.ResourceType,
                            r.RewardModel.ResourceId,
                            r.RewardModel.Amount),
                        PlayerResourceModelFactory.Create(r.RewardModel.PreConversionResource)))
                .ToList();
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveModelFromMissionBonusPoint(
            MissionType missionType,
            IReadOnlyList<MasterDataId> missionIds)
        {
            var bonusPointList = GetMissionBonusPoints(missionType, missionIds);

            if (bonusPointList.IsEmpty())
            {
                return new List<CommonReceiveResourceModel>();
            }

            return bonusPointList
                .Select(bonusPoint =>
                    {
                        var bonusPointResourceModel = PlayerResourceModelFactory.Create(
                            ResourceType.MissionBonusPoint,
                            missionType.ToBonusPointMasterDataId(),
                            bonusPoint.ToPlayerResourceAmount());

                        //ボーナスポイントは所持上限無い想定でNoneを入れる
                        return new CommonReceiveResourceModel(
                            UnreceivedRewardReasonType.None,
                            bonusPointResourceModel,
                            PlayerResourceModel.Empty);
                    }
                )
                .ToList();
        }

        MissionFetchResultModel CreateMissionFetchResultModel(
            MissionAchievementResultModel missionAchievementResultModel,
            MissionDailyBonusResultModel missionDailyBonusResultModel,
            MissionDailyResultModel missionDailyResultModel,
            MissionWeeklyResultModel missionWeeklyResultModel,
            MissionBeginnerResultModel missionBeginnerResultModel)
        {
            var missionModel = MissionCacheRepository.GetMissionModel();
            return new MissionFetchResultModel(
                missionAchievementResultModel,
                missionDailyBonusResultModel,
                missionDailyResultModel,
                missionWeeklyResultModel,
                missionBeginnerResultModel,
                missionModel.BeginnerMissionDaysFromStart);
        }

        GameFetchModel UpdateFetchModel(
            UserParameterModel userParameterModel,
            int receivableMissionCount,
            int receivableMissionBeginnerCount)
        {
            var fetchModel = GameRepository.GetGameFetch();
            var badgeModel = fetchModel.BadgeModel with
            {
                UnreceivedMissionRewardCount = new UnreceivedMissionRewardCount(receivableMissionCount),
                UnreceivedMissionBeginnerRewardCount = new UnreceivedMissionRewardCount(receivableMissionBeginnerCount)
            };
            var updatedFetchModel = fetchModel with
            {
                UserParameterModel = userParameterModel,
                BadgeModel = badgeModel
            };

            return updatedFetchModel;
        }

        GameFetchOtherModel UpdateFetchOtherModel(
            IReadOnlyList<UserConditionPackModel> conditionPackModels,
            IReadOnlyList<UserItemModel> userItemModels,
            IReadOnlyList<UserUnitModel> userUnitModels)
        {
            var fetchOtherModel = GameRepository.GetGameFetchOther();

            var newGameFetchOther = fetchOtherModel with
            {
                UserConditionPackModels = fetchOtherModel.UserConditionPackModels.Update(conditionPackModels),
                UserItemModels = fetchOtherModel.UserItemModels.Update(userItemModels),
                UserUnitModels = fetchOtherModel.UserUnitModels.Update(userUnitModels)
            };

            return newGameFetchOther;
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveResourceModels(
            IReadOnlyList<MissionRewardModel> missionRewardModel)
        {
            return missionRewardModel
                .Select(r =>
                    new CommonReceiveResourceModel(
                        r.RewardModel.UnreceivedRewardReasonType,
                        PlayerResourceModelFactory.Create(
                            r.RewardModel.ResourceType,
                            r.RewardModel.ResourceId,
                            r.RewardModel.Amount),
                        PlayerResourceModelFactory.Create(r.RewardModel.PreConversionResource)))
                .ToList();
        }

        IReadOnlyList<BonusPoint> GetMissionBonusPoints(MissionType missionType, IReadOnlyList<MasterDataId> missionIds)
        {
            switch (missionType)
            {
                case MissionType.Daily:
                    return MissionDataRepository.GetMstMissionDailyModels()
                        .Where(mst => missionIds.Contains(mst.Id) && !mst.BonusPoint.IsZero())
                        .Select(mst => mst.BonusPoint)
                        .ToList();
                case MissionType.Weekly:
                    return MissionDataRepository.GetMstMissionWeeklyModels()
                        .Where(mst => missionIds.Contains(mst.Id) && !mst.BonusPoint.IsZero())
                        .Select(mst => mst.BonusPoint)
                        .ToList();
                case MissionType.Beginner:
                    return MissionDataRepository.GetMstMissionBeginnerModels()
                        .Where(mst => missionIds.Contains(mst.Id) && !mst.BonusPoint.IsZero())
                        .Select(mst => mst.BonusPoint)
                        .ToList();
                default:
                    return new List<BonusPoint>();
            }
        }

        IReadOnlyList<MasterDataId> GetReceivableMissionIds(MissionType missionType)
        {
            var cacheModel = MissionCacheRepository.GetMissionModel();

            switch (missionType)
            {
                case MissionType.Achievement:
                    return cacheModel.UserMissionAchievementModels
                        .Where(model => model.IsCleared && !model.IsReceivedReward)
                        .Select(model => model.MstMissionAchievementId)
                        .ToList();
                case MissionType.Daily:
                    return cacheModel.UserMissionDailyModels
                        .Where(model => model.IsCleared && !model.IsReceivedReward)
                        .Select(model => model.MstMissionDailyId)
                        .ToList();
                case MissionType.Weekly:
                    return cacheModel.UserMissionWeeklyModels
                        .Where(model => model.IsCleared && !model.IsReceivedReward)
                        .Select(model => model.MstMissionWeeklyId)
                        .ToList();
                case MissionType.Beginner:
                    return cacheModel.UserMissionBeginnerModels
                        .Where(model => model.IsCleared && !model.IsReceivedReward)
                        .Select(model => model.MstMissionBeginnerId)
                        .ToList();
                default:
                    throw new Exception("Invalid mission type");
            }
        }

        void SaveReceivedBonusPointMission(
            MissionType missionType,
            MissionBulkReceiveRewardResultModel receiveMissionRewardModel,
            HashSet<MasterDataId> usedMissionIdSetWhenReceiving)
        {
            // 受け取り前のミッションのポイントを取得
            var beforeMissionBonusPointModel = MissionCacheRepository.GetBonusPointMission(missionType);
            var updatedMissionBonusPointModel = receiveMissionRewardModel.UserMissionBonusPointModels
                .FirstOrDefault(model => model.MissionType == missionType, UserMissionBonusPointModel.Empty);

            var bonusPointMissionRewards = receiveMissionRewardModel.MissionRewardModels
                .Where(model => !usedMissionIdSetWhenReceiving.Contains(model.MissionId))
                .ToList();

            var receivedBonusPointMissionRewards = CreateCommonReceiveResourceModels(bonusPointMissionRewards);

            // 受け取った宝箱報酬のミッションIDを取得するため、受け取りで使用したポイントミッションのIDを除外
            var receivedBonusPointMissionIds = bonusPointMissionRewards
                .Select(model => model.MissionId)
                .ToList();

            var receivedBonusPointMissionRewardResultModel = new ReceivedBonusPointMissionRewardResultModel(
                beforeMissionBonusPointModel,
                updatedMissionBonusPointModel,
                receivedBonusPointMissionRewards,
                receivedBonusPointMissionIds);

            MissionCacheRepository.SetReceivedBonusPointMissionRewards(receivedBonusPointMissionRewardResultModel);
            MissionCacheRepository.UpdateBonusPointMission(missionType, receiveMissionRewardModel.UserMissionBonusPointModels);
        }

        IReadOnlyList<MasterDataId> RemoveLockedBeginnerMissionIds(
            IReadOnlyList<MasterDataId> missionIds,
            BeginnerMissionDaysFromStart daysFromStart)
        {
            var lockedBeginnerMissionIds = MissionDataRepository.GetMstMissionBeginnerModels()
                .Where(mst => mst.UnlockDay > daysFromStart)
                .Select(mst => mst.Id);

            var removedMissionIds = missionIds
                .Except(lockedBeginnerMissionIds)
                .ToList();

            return removedMissionIds;
        }
    }
}
