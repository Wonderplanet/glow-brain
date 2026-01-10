using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Const;
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
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.Mission.Domain.Calculator;
using GLOW.Scenes.Mission.Domain.Creator;
using GLOW.Scenes.Mission.Domain.Definition.Service;
using GLOW.Scenes.Mission.Domain.Extension;
using GLOW.Scenes.Mission.Domain.Model;
using GLOW.Scenes.Mission.Domain.Model.AchievementMission;
using GLOW.Scenes.Mission.Domain.Model.DailyBonusMission;
using GLOW.Scenes.Mission.Domain.Model.DailyMission;
using GLOW.Scenes.Mission.Domain.Model.WeeklyMission;
using GLOW.Scenes.Mission.Domain.Translator;
using WPFramework.Constants.MasterData;
using Zenject;

namespace GLOW.Scenes.Mission.Domain.UseCase
{
    public class ReceiveMissionRewardUseCase
    {
        [Inject] IMissionService MissionService { get; }
        [Inject] IMstMissionDataRepository MissionDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMissionCacheRepository MissionCacheRepository { get; }
        [Inject] IUserLevelUpCacheRepository UserLevelUpCacheRepository { get; }
        [Inject] IMissionResultModelFactory MissionResultModelFactory { get; }

        public async UniTask<ReceiveMissionRewardUseCaseModel> ReceiveMissionReward(
            CancellationToken cancellationToken,
            MissionType missionType,
            MasterDataId missionId)
        {
            var receiveMissionRewardModel = await MissionService.ReceiveReward(cancellationToken, missionType, missionId);

            foreach (var receiveRewardModel in receiveMissionRewardModel.MissionReceiveRewardModels)
            {
                //CacheRepository副作用
                MissionCacheRepository.UpdateMissionStatus(
                    receiveRewardModel.MissionType,
                    receiveRewardModel.MstMissionId,
                    MissionStatus.Received);
            }

            if (missionType.ExistBonusPointMission())
            {
                //CacheRepository副作用
                SaveReceivedBonusPointMission(missionType, receiveMissionRewardModel, missionId);
            }

            var missionModel = MissionCacheRepository.GetMissionModel();
            var userMissionAchievement = missionModel.UserMissionAchievementModels;
            var userMissionBonusPoint = missionModel.UserMissionBonusPointModels;
            var userMissionDaily = missionModel.UserMissionDailyModels;
            var userMissionWeekly = missionModel.UserMissionWeeklyModels;
            var userMissionBeginner = missionModel.UserMissionBeginnerModels;
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
                userMissionBeginner,
                userMissionBonusPoint);


            //副作用
            ApplyUpdateFetchModel(
                receiveMissionRewardModel,
                missionAchievementResultModel,
                missionDailyBonusResultModel,
                missionDailyResultModel,
                missionWeeklyResultModel,
                missionBeginnerResultModel,
                missionModel.BeginnerMissionDaysFromStart);

            var prevUserParameterModel = GameRepository.GetGameFetch().UserParameterModel;

            // 副作用
            // 経験値を受け取れる関係でレベルアップする可能性があるため
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

            var commonReceiveResourceModels =
                CreateCommonReceiveModelFromMissionRewardModel(missionId, receiveMissionRewardModel.MissionRewardModels)
                    .Concat(CreateCommonReceiveModelFromMissionBonusPoint(missionType, missionId))
                    .ToList();

            return new ReceiveMissionRewardUseCaseModel(
                commonReceiveResourceModels,
                missionFetchResultModel
            );
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveModelFromMissionRewardModel(
            MasterDataId missionId,
            IReadOnlyList<MissionRewardModel> missionRewardModels)
        {
            return missionRewardModels
                // 獲得したポイントのミッションと同じミッションIdで獲得した報酬を取得(ボーナスポイント報酬など、ほかが混ざってくることがある)
                .Where(model => model.MissionId == missionId)
                .Select(m =>
                    new CommonReceiveResourceModel(
                        m.RewardModel.UnreceivedRewardReasonType,
                        PlayerResourceModelFactory.Create(
                            m.RewardModel.ResourceType,
                            m.RewardModel.ResourceId,
                            m.RewardModel.Amount),
                        PlayerResourceModelFactory.Create(m.RewardModel.PreConversionResource)))
                .ToList();
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveModelFromMissionBonusPoint(
            MissionType missionType,
            MasterDataId missionId)
        {
            // ボーナスポイント獲得可能ミッションでないときは、何もしない
            if (!missionType.ExistBonusPointMission())
            {
                return new List<CommonReceiveResourceModel>();
            }

            var bonusPoint = GetMissionBonusPoint(missionType, missionId);
            return CreatePlayerResourceBonusPointModels(bonusPoint, missionType.ToBonusPointMasterDataId());
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


        void ApplyUpdateFetchModel(
            MissionBulkReceiveRewardResultModel receiveMissionRewardModel,
            MissionAchievementResultModel missionAchievementResultModel,
            MissionDailyBonusResultModel missionDailyBonusResultModel,
            MissionDailyResultModel missionDailyResultModel,
            MissionWeeklyResultModel missionWeeklyResultModel,
            MissionBeginnerResultModel missionBeginnerResultModel,
            BeginnerMissionDaysFromStart beginnerMissionDaysFromStart
        )
        {
            var calculatedReceivableMissionCount = ReceivableMissionCountCalculator.GetReceivableMissionCount(
                missionAchievementResultModel,
                missionDailyBonusResultModel,
                missionDailyResultModel,
                missionWeeklyResultModel);

            var calculatedReceivableMissionBeginnerCount = ReceivableMissionCountCalculator.GetReceivableMissionBeginnerCount(
                missionBeginnerResultModel,
                beginnerMissionDaysFromStart);

            var updatedGameFetchModel = CreateUpdatedFetchModel(
                receiveMissionRewardModel,
                calculatedReceivableMissionCount,
                calculatedReceivableMissionBeginnerCount);
            var updatedGameFetchOtherModel = CreateUpdatedFetchOtherModel(receiveMissionRewardModel);

            GameManagement.SaveGameUpdateAndFetch(updatedGameFetchModel, updatedGameFetchOtherModel);
        }


        GameFetchModel CreateUpdatedFetchModel(
            MissionBulkReceiveRewardResultModel missionReceiveRewardResult,
            int receivableMissionCount,
            int receivableMissionBeginnerCount)
        {
            var fetchModel = GameRepository.GetGameFetch();

            var updatedUserParameterModel = missionReceiveRewardResult.UserParameterModel;
            var badgeModel = fetchModel.BadgeModel with
            {
                UnreceivedMissionRewardCount = new UnreceivedMissionRewardCount(receivableMissionCount),
                UnreceivedMissionBeginnerRewardCount = new UnreceivedMissionRewardCount(receivableMissionBeginnerCount)
            };

            var updatedFetchModel = fetchModel with
            {
                UserParameterModel = updatedUserParameterModel,
                BadgeModel = badgeModel
            };

            return updatedFetchModel;
        }

        GameFetchOtherModel CreateUpdatedFetchOtherModel(MissionBulkReceiveRewardResultModel resultModel)
        {
            var fetchOtherModel = GameRepository.GetGameFetchOther();

            var userEmblemModels = resultModel.MissionRewardModels
                .Where(r => r.RewardModel.ResourceType == ResourceType.Emblem)
                .Select(r => new UserEmblemModel(r.RewardModel.ResourceId, NewEncyclopediaFlag.True))
                .ToList();

            var newGameFetchOther = fetchOtherModel with
            {
                UserConditionPackModels = fetchOtherModel.UserConditionPackModels.Update(resultModel.ConditionPackModels),
                UserEmblemModel = fetchOtherModel.UserEmblemModel.Update(userEmblemModels),
                UserItemModels = fetchOtherModel.UserItemModels.Update(resultModel.UserItemModels),
                UserUnitModels = fetchOtherModel.UserUnitModels.Update(resultModel.UserUnitModels)
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

        IReadOnlyList<CommonReceiveResourceModel> CreatePlayerResourceBonusPointModels(
            BonusPoint bonusPoint,
            MasterDataId constBonusPointMstId)
        {
            return new List<CommonReceiveResourceModel>()
            {
                //ボーナスポイントは所持上限無い想定でNoneを入れる
                new(
                    UnreceivedRewardReasonType.None,
                    PlayerResourceModelFactory.Create(
                        ResourceType.MissionBonusPoint,
                        constBonusPointMstId,
                        bonusPoint.ToPlayerResourceAmount()),
                    PlayerResourceModel.Empty)
            };
        }

        BonusPoint GetMissionBonusPoint(MissionType missionType, MasterDataId missionId)
        {
            switch (missionType)
            {
                case MissionType.Daily:
                    return MissionDataRepository.GetMstMissionDailyModels()
                        .FirstOrDefault(model => model.Id == missionId, MstMissionDailyModel.Empty)
                        .BonusPoint;

                case MissionType.Weekly:
                    return MissionDataRepository.GetMstMissionWeeklyModels()
                        .FirstOrDefault(model => model.Id == missionId, MstMissionWeeklyModel.Empty)
                        .BonusPoint;

                case MissionType.Beginner:
                    return MissionDataRepository.GetMstMissionBeginnerModels()
                        .FirstOrDefault(model => model.Id == missionId, MstMissionBeginnerModel.Empty)
                        .BonusPoint;

                default:
                    return BonusPoint.Empty;
            }
        }

        void SaveReceivedBonusPointMission(
            MissionType missionType,
            MissionBulkReceiveRewardResultModel receiveMissionRewardModel,
            MasterDataId usedMissionIdWhenReceiving)
        {
            // 受け取り前のミッションのポイントを取得
            var beforeMissionBonusPointModel = MissionCacheRepository.GetBonusPointMission(missionType);
            var updatedMissionBonusPointModel = receiveMissionRewardModel.UserMissionBonusPointModels
                .FirstOrDefault(model => model.MissionType == missionType, UserMissionBonusPointModel.Empty);

            var bonusPointMissionRewards = receiveMissionRewardModel.MissionRewardModels
                .Where(model => model.MissionId != usedMissionIdWhenReceiving)
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
    }
}
