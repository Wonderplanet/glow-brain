using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Calculator;
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
using GLOW.Scenes.EventMission.Domain.Model;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.Mission.Domain.Calculator;
using GLOW.Scenes.Mission.Domain.Creator;
using GLOW.Scenes.Mission.Domain.Definition.Service;
using Zenject;

namespace GLOW.Scenes.EventMission.Domain.UseCase
{
    public class ReceiveEventMissionRewardUseCase
    {
        [Inject] IMissionService MissionService { get; }
        [Inject] IMstMissionDataRepository MissionDataRepository { get; }
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMissionEventCacheRepository MissionEventCacheRepository { get; }
        [Inject] IUserLevelUpCacheRepository UserLevelUpCacheRepository { get; }
        [Inject] IMissionResultModelFactory MissionResultModelFactory { get; }

        public async UniTask<ReceiveEventMissionRewardUseCaseModel> ReceiveEventMissionReward(
            CancellationToken cancellationToken,
            MissionType missionType,
            MasterDataId missionId,
            MasterDataId mstEventId,
            MasterDataId displayMissionMstEventId)
        {
            // サーバー通信
            var receiveMissionRewardModel = await MissionService.ReceiveReward(
                cancellationToken,
                missionType,
                missionId);

            // 副作用
            MissionEventCacheRepository.UpdateMissionStatus(mstEventId, missionType, missionId, MissionStatus.Received);
            
            var mstEvents = MstEventDataRepository.GetEvents();
            var eventAchievementModel = CreateEventMissionAchievementResultModel(mstEvents);

            // 順番依存.1(ApplyUpdateFetchModelで更新されるため、その前に情報を保持しておく)
            var prevUserParameterModel = GameRepository.GetGameFetch().UserParameterModel;

            // イベント情報の取得(イベントTOPからの表示で指定されている場合はそのイベントのみ)
            // 順番依存.2(ここでフィルタリングしないとApplyUpdatedFetchModelでバッジ数が正しく計算されない)
            if (!displayMissionMstEventId.IsEmpty())
            {
                eventAchievementModel = eventAchievementModel with
                {
                    OpeningEventAchievementCellModels = eventAchievementModel.OpeningEventAchievementCellModels
                        .Where(cell => cell.EventId == displayMissionMstEventId)
                        .ToList()
                };
            }
            
            //副作用 / 順番依存.3
            ApplyUpdatedFetchModel(
                mstEventId,
                receiveMissionRewardModel,
                eventAchievementModel);

            // 副作用。経験値を受け取れる関係でレベルアップする可能性がある
            UserLevelUpCacheRepository.Save(
                receiveMissionRewardModel.UserLevelUpModel,
                prevUserParameterModel.Level,
                prevUserParameterModel.Exp);

            var targetMstEvents = mstEvents
                .Where(m => m.Id == mstEventId)
                .ToList();
            var newestMstEvent = targetMstEvents.MaxBy(m => m.StartAt);

            var missionFetchResultModel = new EventMissionFetchResultModel(
                newestMstEvent.Id,
                eventAchievementModel,
                EventMissionDailyBonusResultModel.Empty);


            return new ReceiveEventMissionRewardUseCaseModel(
                CreateCommonReceiveModel(receiveMissionRewardModel.MissionRewardModels),
                missionFetchResultModel);
        }

        EventMissionAchievementResultModel CreateEventMissionAchievementResultModel(
            IReadOnlyList<MstEventModel> mstEvents)
        {
            var mstEventIds = mstEvents
                .Where(model => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, model.StartAt, model.EndAt))
                .Select(model => model.Id)
                .ToList();

            var missionModels = mstEventIds
                .Select(mstId => MissionEventCacheRepository.GetMissionEventModelOrDefault(mstId))
                .ToList();

            var userEventAchievementModels = missionModels
                .SelectMany(m => m.UserMissionEventModels)
                .ToList();

            return MissionResultModelFactory.CreateEventMissionAchievementResultModel(
                mstEvents,
                TimeProvider,
                MissionDataRepository,
                MissionDataRepository,
                PlayerResourceModelFactory,
                userEventAchievementModels);
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveModel(IReadOnlyList<MissionRewardModel> models)
        {
            return models
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

        void ApplyUpdatedFetchModel(
            MasterDataId mstEventId,
            MissionBulkReceiveRewardResultModel receiveMissionRewardModel,
            EventMissionAchievementResultModel eventAchievementModel)
        {
            // この時点でeventAchievementModelは表示しているイベントのみの情報になっているので、それに対してバッジ数計算を行う
            var eventMissionBadge = ReceivableMissionCountCalculator.GetReceivableMissionEventCount(
                eventAchievementModel);

            var updatedFetchModel = UpdateFetchModel(receiveMissionRewardModel.UserParameterModel, mstEventId, eventMissionBadge);
            var updatedFetchOtherModel = UpdateFetchOtherModel(receiveMissionRewardModel);
            GameManagement.SaveGameUpdateAndFetch(updatedFetchModel, updatedFetchOtherModel);
        }

        GameFetchModel UpdateFetchModel(
            UserParameterModel userParameterModel, 
            MasterDataId mstEventId,
            int receivableMissionEventCount)
        {
            var fetchModel = GameRepository.GetGameFetch();
            
            // BadgeModelの更新
            var updatedBadgeModel = GetUpdatedBadgeModel(
                fetchModel.BadgeModel,
                mstEventId,
                receivableMissionEventCount);

            var updatedFetchModel = fetchModel with
            {
                UserParameterModel = userParameterModel,
                BadgeModel = updatedBadgeModel
            };

            return updatedFetchModel;
        }

        GameFetchOtherModel UpdateFetchOtherModel(MissionBulkReceiveRewardResultModel resultModel)
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

        BadgeModel GetUpdatedBadgeModel(BadgeModel beforeBadgeModel, MasterDataId mstEventId, int receivableMissionEventCount)
        {
            var badgeModel = beforeBadgeModel;
            var updatedEventMissionBadgeModel = new MissionEventRewardCountModel(
                mstEventId,
                new UnreceivedMissionRewardCount(receivableMissionEventCount));
            var updatedBadgeModel = badgeModel with
            {
                UnreceivedMissionEventRewardCounts = badgeModel.UnreceivedMissionEventRewardCounts.Update(
                    updatedEventMissionBadgeModel)
            };
            
            return updatedBadgeModel;
        }
    }
}
