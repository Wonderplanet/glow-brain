using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Loader;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using GLOW.Scenes.Mission.Domain.Constant;
using GLOW.Scenes.Mission.Domain.Creator;
using GLOW.Scenes.Mission.Domain.Definition.Service;
using GLOW.Scenes.Mission.Domain.Model;
using Zenject;

namespace GLOW.Scenes.Mission.Domain.UseCase
{
    public class FetchMissionListUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMissionService MissionService { get; }
        [Inject] IMstMissionDataRepository MissionDataRepository { get; }
        [Inject] IMissionCacheRepository MissionCacheRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMissionResultModelFactory MissionResultModelFactory { get; }
        [Inject] IReceivedDailyBonusRewardLoader ReceivedDailyBonusRewardLoader { get; }

        public async UniTask<MissionFetchResultModel> FetchMissionList(CancellationToken cancellationToken)
        {
            var updateAndFetchResultModel = await MissionService.UpdateAndFetch(cancellationToken);

            // キャッシュ保存
            var missionModel = CreateMissionModel(updateAndFetchResultModel);
            MissionCacheRepository.SetMissionModel(missionModel);

            var userMissionAchievement = missionModel.UserMissionAchievementModels;
            var userMissionBonusPoint = missionModel.UserMissionBonusPointModels;
            var userMissionDaily = missionModel.UserMissionDailyModels;
            var userMissionWeekly = missionModel.UserMissionWeeklyModels;
            var userMissionBeginner = missionModel.UserMissionBeginnerModels;
            var userMissionDailyBonus = missionModel.UserMissionDailyBonusModels;

            var missionAchievementResultModel =
                MissionResultModelFactory.CreateMissionAchievementResultModel(
                    MissionDataRepository,
                    PlayerResourceModelFactory,
                    userMissionAchievement);

            // 受け取ったデイリーボーナスの報酬をロード(端末保存されている場合はその情報がロードされる)
            ReceivedDailyBonusRewardLoader.LoadReceivedDailyBonusRewards();

            var gameFetchOtherModel = GameRepository.GetGameFetchOther();

            var missionDailyBonusResultModel =
                MissionResultModelFactory.CreateMissionDailyBonusResultModel(
                MissionDataRepository,
                PlayerResourceModelFactory,
                userMissionDailyBonus,
                gameFetchOtherModel.UserLoginInfoModel,
                gameFetchOtherModel.MissionReceivedDailyBonusModel);
            var missionDailyResultModel =
                MissionResultModelFactory.CreateMissionDailyResultModel(
                MissionDataRepository,
                PlayerResourceModelFactory,
                userMissionDaily,
                userMissionBonusPoint);
            var missionWeeklyResultModel =
                MissionResultModelFactory.CreateMissionWeeklyResultModel(
                    MissionDataRepository,
                    PlayerResourceModelFactory,userMissionWeekly,
                    userMissionBonusPoint);
            var missionBeginnerResultModel =
                MissionResultModelFactory.CreateMissionBeginnerResultModel(
                    MissionDataRepository,
                    PlayerResourceModelFactory,
                    userMissionBeginner,
                    userMissionBonusPoint);

            return new MissionFetchResultModel(
                missionAchievementResultModel,
                missionDailyBonusResultModel,
                missionDailyResultModel,
                missionWeeklyResultModel,
                missionBeginnerResultModel,
                updateAndFetchResultModel.BeginnerMissionDaysFromStart);
        }

        MissionModel CreateMissionModel(MissionUpdateAndFetchResultModel missionUpdateAndFetchResultModel)
        {
            var achievementId = MissionDataRepository.GetMstMissionAchievementModels().Select(mst => mst.Id).ToList();
            var achievementModel = achievementId.Select(id =>
            {
                var userModel = missionUpdateAndFetchResultModel.UserMissionAchievementModels?.Find(user =>
                    user.MstMissionAchievementId == id);
                return userModel ?? MissionModel.AchievementEmpty(id);
            }).ToList();

            var dailyBonusId = MissionDataRepository.GetMstMissionDailyBonusModels().Select(mst => mst.Id).ToList();
            var dailyBonusModel = dailyBonusId.Select(id =>
            {
                var userModel = missionUpdateAndFetchResultModel.UserMissionDailyBonusModels?.Find(user =>
                    user.MstMissionDailyBonusId == id);
                return userModel ?? MissionModel.DailyBonusEmpty(id);
            }).ToList();

            var dailyId = MissionDataRepository.GetMstMissionDailyModels().Where(mst => mst.CriterionType != MissionConst.BonusPointCriterionType).Select(mst => mst.Id).ToList();
            var dailyModel = dailyId.Select(id =>
            {
                var userModel = missionUpdateAndFetchResultModel.UserMissionDailyModels?.Find(user =>
                    user.MstMissionDailyId == id);
                return userModel ?? MissionModel.DailyEmpty(id);
            }).ToList();

            var weeklyId = MissionDataRepository.GetMstMissionWeeklyModels().Where(mst => mst.CriterionType != MissionConst.BonusPointCriterionType).Select(mst => mst.Id).ToList();
            var weeklyModel = weeklyId.Select(id =>
            {
                var userModel = missionUpdateAndFetchResultModel.UserMissionWeeklyModels?.Find(user =>
                    user.MstMissionWeeklyId == id);
                return userModel ?? MissionModel.WeeklyEmpty(id);
            }).ToList();

            var beginnerId = MissionDataRepository.GetMstMissionBeginnerModels().Where(mst => mst.CriterionType != MissionConst.BonusPointCriterionType).Select(mst => mst.Id).ToList();
            var beginnerModel = beginnerId.Select(id =>
            {
                var userModel = missionUpdateAndFetchResultModel.UserMissionBeginnerModels?.Find(user =>
                    user.MstMissionBeginnerId == id);
                return userModel ?? MissionModel.BeginnerEmpty(id);
            }).ToList();

            var bonusPointTypeList = new List<MissionType>()
                { MissionType.Daily, MissionType.Weekly, MissionType.DailyBonus, MissionType.Beginner };
            var bonusPointModel = bonusPointTypeList.Select(type =>
            {
                var userModel = missionUpdateAndFetchResultModel.UserMissionBonusPointModels?.Find(user =>
                    user.MissionType == type);
                return userModel ?? MissionModel.BonusPointEmpty(type);
            }).ToList();

            return new MissionModel(
                achievementModel, dailyBonusModel, dailyModel, weeklyModel, beginnerModel, missionUpdateAndFetchResultModel.BeginnerMissionDaysFromStart, bonusPointModel);
        }
    }
}
