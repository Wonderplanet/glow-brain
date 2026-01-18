using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.Mission.Domain.Creator;
using GLOW.Scenes.Mission.Domain.Model;
using Zenject;

namespace GLOW.Scenes.Mission.Domain.UseCase
{
    public class AutoReceiveDailyBonusUseCase
    {
        [Inject] IMstMissionDataRepository MissionDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMissionCacheRepository MissionCacheRepository { get; }
        [Inject] IMissionResultModelFactory MissionResultModelFactory { get; }

        public ReceivedDailyBonusInfoModel GetAutoReceiveDailyBonus()
        {
            // 今のキャッシュを取得
            var missionModel = MissionCacheRepository.GetMissionModel();
            var userMissionAchievement = missionModel.UserMissionAchievementModels;
            var userMissionBonusPoint = missionModel.UserMissionBonusPointModels;
            var userMissionDaily = missionModel.UserMissionDailyModels;
            var userMissionWeekly = missionModel.UserMissionWeeklyModels;
            var userMissionBeginner = missionModel.UserMissionBeginnerModels;
            var userMissionDailyBonus = missionModel.UserMissionDailyBonusModels;

            // 自動受け取りした報酬の情報
            var receivedDailyBonusModels = GameRepository.GetGameFetchOther()
                .MissionReceivedDailyBonusModel;
            if (receivedDailyBonusModels.IsEmpty())
            {
                return ReceivedDailyBonusInfoModel.Empty;
            }

            // 通常ログインボーナス
            var receivedDailyBonusRewards = receivedDailyBonusModels
                .Where(model => model.MissionType == MissionDailyBonusType.DailyBonus)
                .ToList();
            var dailyBonusResources = CreateCommonReceiveModels(receivedDailyBonusRewards);

            // 受け取った時の日数を取得
            // ここの関数を呼ぶ前にreceivedDailyBonus空かどうかの判定しているため、呼ばれる時点で必ず1つ以上の要素がある
            // 受け取るログインボーナスは1日にDailyBonusが1つ、TotalBonusが最大1つなのでDailyBonusをFirstで指定して取得できる。
            var receivedDailyBonus = receivedDailyBonusRewards.FirstOrDefault(MissionReceivedDailyBonusModel.Empty);

            // 受け取り済みの表示をするため、キャッシュからログインボーナス分をクリア
            ClearDailyBonusReward();

            // キャッシュの情報を更新
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            var missionAchievementResultModel =
                MissionResultModelFactory.CreateMissionAchievementResultModel(
                    MissionDataRepository,
                    PlayerResourceModelFactory,
                    userMissionAchievement);

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
                    PlayerResourceModelFactory,
                    userMissionWeekly,
                    userMissionBonusPoint);

            var missionBeginnerResultModel =
                MissionResultModelFactory.CreateMissionBeginnerResultModel(
                    MissionDataRepository,
                    PlayerResourceModelFactory,
                    userMissionBeginner,
                    userMissionBonusPoint);

            // 更新後のミッションリスト情報を作り直す
            var resultModel = new MissionFetchResultModel(
                missionAchievementResultModel,
                missionDailyBonusResultModel,
                missionDailyResultModel,
                missionWeeklyResultModel,
                missionBeginnerResultModel,
                missionModel.BeginnerMissionDaysFromStart);

            return new ReceivedDailyBonusInfoModel(
                resultModel,
                receivedDailyBonus.LoginDayCount,
                dailyBonusResources);
        }

        void ClearDailyBonusReward()
        {
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            var newGameFetchOther = gameFetchOtherModel with
            {
                MissionReceivedDailyBonusModel = new List<MissionReceivedDailyBonusModel>()
            };

            GameManagement.SaveGameFetchOther(newGameFetchOther);
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveModels(
            IReadOnlyList<MissionReceivedDailyBonusModel> missionRewardModel)
        {
            return missionRewardModel.Select(model =>
                    new CommonReceiveResourceModel(
                        model.RewardModel.UnreceivedRewardReasonType,
                        PlayerResourceModelFactory.Create(
                            model.RewardModel.ResourceType,
                            model.RewardModel.ResourceId,
                            model.RewardModel.Amount),
                        PlayerResourceModelFactory.Create(model.RewardModel.PreConversionResource))
                )
                .ToList();
        }
    }
}
