using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Extensions;
using GLOW.Scenes.Mission.Domain.Model;

namespace GLOW.Core.Data.Repositories
{
    public class MissionCacheRepository : IMissionCacheRepository
    {
        MissionModel _missionModel;
        ReceivedBonusPointMissionRewardResultModel _receivedBonusPointMissionRewards;

        void IMissionCacheRepository.SetMissionModel(MissionModel missionModel)
        {
            _missionModel = missionModel;
        }

        MissionModel IMissionCacheRepository.GetMissionModel()
        {
            return _missionModel;
        }

        void IMissionCacheRepository.SetReceivedBonusPointMissionRewards(ReceivedBonusPointMissionRewardResultModel receivedBonusPointMissionRewardResultModel)
        {
            _receivedBonusPointMissionRewards = receivedBonusPointMissionRewardResultModel;
        }

        ReceivedBonusPointMissionRewardResultModel IMissionCacheRepository.GetReceivedBonusPointMissionRewards()
        {
            return _receivedBonusPointMissionRewards;
        }

        void IMissionCacheRepository.ClearBonusPointMissionRewards()
        {
            _receivedBonusPointMissionRewards = ReceivedBonusPointMissionRewardResultModel.Empty;
        }

        public void UpdateMissionStatus(MissionType missionType, MasterDataId missionId,
            MissionStatus missionStatus)
        {
            switch (missionType)
            {
                case MissionType.Achievement:
                    UpdateAchievementMissionStatus(missionId, missionStatus);
                    break;
                case MissionType.Daily:
                    UpdateDailyMissionStatus(missionId, missionStatus);
                    break;
                case MissionType.DailyBonus:
                    UpdateDailyBonusMissionStatus(missionId, missionStatus);
                    break;
                case MissionType.Weekly:
                    UpdateWeeklyMissionStatus(missionId, missionStatus);
                    break;
                case MissionType.Beginner:
                    UpdateBeginnerMissionStatus(missionId, missionStatus);
                    break;
                default:
                    throw new ArgumentOutOfRangeException(nameof(missionType), missionType, null);
            }
        }
        
        public void UpdateBonusPointMissionProgress(MissionType missionType, BonusPoint progress)
        {
            var missionBonusPoint = _missionModel.UserMissionBonusPointModels;
            var updateMissionBonusPointIndex =
                missionBonusPoint.FindIndex(mst =>
                    mst.MissionType == missionType);
            if (updateMissionBonusPointIndex == -1) return;
            
            var currentReceivedRewardPoints = missionBonusPoint[updateMissionBonusPointIndex].ReceivedRewardPoints;
            missionBonusPoint[updateMissionBonusPointIndex] = new UserMissionBonusPointModel(missionType, progress, currentReceivedRewardPoints);
        }

        public UserMissionBonusPointModel GetBonusPointMission(MissionType missionType)
        {
            var missionBonusPoint = _missionModel.UserMissionBonusPointModels;
            var missionBonusPointIndex =
                missionBonusPoint.FindIndex(mst =>
                    mst.MissionType == missionType);
            if (missionBonusPointIndex == -1) return UserMissionBonusPointModel.Empty;

            return missionBonusPoint[missionBonusPointIndex];
        }

        public void UpdateBonusPointMission(MissionType missionType, IReadOnlyList<UserMissionBonusPointModel> userMissionBonusPointModels)
        {
            var missionBonusPoint = _missionModel.UserMissionBonusPointModels;
            var updateMissionBonusPointIndex =
                missionBonusPoint.FindIndex(mst =>
                    mst.MissionType == missionType);
            if (updateMissionBonusPointIndex == -1) return;
            
            var updateUserMissionBonusPointModelIndex  = userMissionBonusPointModels.FindIndex(bonusPoint => bonusPoint.MissionType == missionType);
            if (updateUserMissionBonusPointModelIndex == -1) return;
            missionBonusPoint[updateMissionBonusPointIndex] = userMissionBonusPointModels[updateUserMissionBonusPointModelIndex];
        }

        void UpdateAchievementMissionStatus(MasterDataId missionId, MissionStatus missionStatus)
        {
            var missionAchievement = _missionModel.UserMissionAchievementModels;
            var updateMissionIndex =
                missionAchievement.FindIndex(mst =>
                    mst.MstMissionAchievementId == missionId);
            if (updateMissionIndex == -1) return;
            
            var progress = missionAchievement[updateMissionIndex].Progress;
            var isClear = missionStatus.IsClearedStatus();
            var isReceivedReward = missionStatus.IsReceivedStatus();
            missionAchievement[updateMissionIndex] = new UserMissionAchievementModel(missionId, progress, isClear, isReceivedReward);
        }
        
        void UpdateDailyMissionStatus(MasterDataId missionId, MissionStatus missionStatus)
        {
            var missionDaily = _missionModel.UserMissionDailyModels;
            var updateMissionIndex =
                missionDaily.FindIndex(mst =>
                    mst.MstMissionDailyId == missionId);
            if (updateMissionIndex == -1) return;
            
            var progress = missionDaily[updateMissionIndex].Progress;
            var isClear = missionStatus.IsClearedStatus();
            var isReceivedReward = missionStatus.IsReceivedStatus();
            missionDaily[updateMissionIndex] = new UserMissionDailyModel(missionId, progress, isClear, isReceivedReward);
        }
        
        void UpdateDailyBonusMissionStatus(MasterDataId missionId, MissionStatus missionStatus)
        {
            var missionDailyBonus = _missionModel.UserMissionDailyBonusModels;
            var updateMissionIndex =
                missionDailyBonus.FindIndex(mst =>
                    mst.MstMissionDailyBonusId == missionId);
            if (updateMissionIndex == -1) return;
            
            var progress = missionDailyBonus[updateMissionIndex].Progress;
            var isClear = missionStatus.IsClearedStatus();
            var isReceivedReward = missionStatus.IsReceivedStatus();
            missionDailyBonus[updateMissionIndex] = new UserMissionDailyBonusModel(missionId, progress, isClear, isReceivedReward);
        }
        
        void UpdateWeeklyMissionStatus(MasterDataId missionId, MissionStatus missionStatus)
        {
            var missionWeekly = _missionModel.UserMissionWeeklyModels;
            var updateMissionIndex =
                missionWeekly.FindIndex(mst =>
                    mst.MstMissionWeeklyId == missionId);
            if (updateMissionIndex == -1) return;
            
            var progress = missionWeekly[updateMissionIndex].Progress;
            var isClear = missionStatus.IsClearedStatus();
            var isReceivedReward = missionStatus.IsReceivedStatus();
            missionWeekly[updateMissionIndex] = new UserMissionWeeklyModel(missionId, progress, isClear, isReceivedReward);
        }
        
        void UpdateBeginnerMissionStatus(MasterDataId missionId, MissionStatus missionStatus)
        {
            var missionBeginner = _missionModel.UserMissionBeginnerModels;
            var updateMissionIndex =
                missionBeginner.FindIndex(mst =>
                    mst.MstMissionBeginnerId == missionId);
            if (updateMissionIndex == -1) return;
            
            var progress = missionBeginner[updateMissionIndex].Progress;
            var isClear = missionStatus.IsClearedStatus();
            var isReceivedReward = missionStatus.IsReceivedStatus();
            missionBeginner[updateMissionIndex] = new UserMissionBeginnerModel(missionId, progress, isClear, isReceivedReward);
        }
    }
}