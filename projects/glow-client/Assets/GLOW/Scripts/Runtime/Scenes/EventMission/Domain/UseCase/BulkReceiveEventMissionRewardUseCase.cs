using System;
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
    public class BulkReceiveEventMissionRewardUseCase
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

        public async UniTask<BulkReceiveEventMissionRewardUseCaseModel> BulkReceiveMissionReward(
            CancellationToken cancellationToken,
            IReadOnlyList<MasterDataId> receiveMissionEventIds,
            MasterDataId displayMissionMstEventId)
        {
            var receivableMissionIds = GetReceivableEventMissionIds(MissionType.Event, receiveMissionEventIds);

            // サーバー通信
            var receiveMissionRewardModel = await MissionService.BulkReceiveReward(
                cancellationToken,
                MissionType.Event,
                receivableMissionIds);

            // イベントのIDとミッションIDのペア
            var mstEventIdAndMstMissionIdPairs = CreateMstEventIdAndMstMissionIdPairs(receiveMissionRewardModel);
            
            // ミッションの更新(副作用)
            UpdateMissionStatus(mstEventIdAndMstMissionIdPairs);
            
            // 全ての開催中のイベントの情報
            var mstEvents = MstEventDataRepository.GetEvents();
            var mstEventIds = mstEvents
                .Where(model => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, model.StartAt, model.EndAt))
                .Select(model => model.Id)
                .ToList();
            
            var eventAchievementModel = CreateEventMissionAchievementResultModel(mstEventIds, mstEvents);

            //順番依存.1
            var prevUserParameterModel = GameRepository.GetGameFetch().UserParameterModel;
            
            // 受け取ったミッションのイベントIDとそれに紐づく受け取ったミッション数
            var rewardCountModels = CreateMissionEventRewardCountModels(
                receiveMissionEventIds,
                mstEventIdAndMstMissionIdPairs,
                eventAchievementModel);

            // 副作用 / 順番依存.2
            ApplyUpdatedFetchModel(
                receiveMissionRewardModel,
                rewardCountModels);
            
            // イベント情報の取得(イベントTOPからの表示で指定されている場合はそのイベントのみ)
            if (!displayMissionMstEventId.IsEmpty())
            {
                eventAchievementModel = eventAchievementModel with
                {
                    OpeningEventAchievementCellModels = eventAchievementModel.OpeningEventAchievementCellModels
                        .Where(cell => cell.EventId == displayMissionMstEventId)
                        .ToList()
                };
            }

            // 副作用。経験値を受け取れる関係でレベルアップする可能性がある
            UserLevelUpCacheRepository.Save(
                receiveMissionRewardModel.UserLevelUpModel,
                prevUserParameterModel.Level,
                prevUserParameterModel.Exp);

            return new BulkReceiveEventMissionRewardUseCaseModel(
                CreateCommonReceiveModel(receiveMissionRewardModel.MissionRewardModels),
                CreateEventMissionFetchResultModel(
                    mstEvents,
                    eventAchievementModel));
        }
        
        IReadOnlyList<(MasterDataId mstEventId, MasterDataId mstMissionId)> CreateMstEventIdAndMstMissionIdPairs(
            MissionBulkReceiveRewardResultModel receiveMissionRewardModel)
        {
            return receiveMissionRewardModel.MissionReceiveRewardModels
                .Join(
                    MissionDataRepository.GetMstMissionEventModels(),
                    r => r.MstMissionId,
                    m => m.Id,
                    (r, m) => ( m.MstEventId, r.MstMissionId ))
                .ToList();
        }

        void UpdateMissionStatus(IReadOnlyList<(MasterDataId mstEventId, MasterDataId mstMissionId)> mstEventIdAndMstMissionIdPairs)
        {
            foreach (var receivedMissionId in mstEventIdAndMstMissionIdPairs)
            {
                MissionEventCacheRepository.UpdateMissionStatus(
                    receivedMissionId.mstEventId,
                    MissionType.Event,
                    receivedMissionId.mstMissionId,
                    MissionStatus.Received);
            }
        }

        EventMissionAchievementResultModel CreateEventMissionAchievementResultModel(
            IReadOnlyList<MasterDataId> mstEventIds,
            IReadOnlyList<MstEventModel> mstEvents)
        {
            var missionModels = mstEventIds
                .Select(mstId => MissionEventCacheRepository.GetMissionEventModelOrDefault(mstId));

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

        EventMissionFetchResultModel CreateEventMissionFetchResultModel(
            IReadOnlyList<MstEventModel> mstEvents,
            EventMissionAchievementResultModel eventAchievementModel)
        {
            var openingMstEventModels = mstEvents
                .Where(m => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, m.StartAt, m.EndAt))
                .ToList();
            var newestMstEvent = openingMstEventModels.MaxBy(m => m.StartAt);

            return new EventMissionFetchResultModel(
                newestMstEvent.Id,
                eventAchievementModel,
                EventMissionDailyBonusResultModel.Empty);
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
        
        IReadOnlyList<MissionEventRewardCountModel> CreateMissionEventRewardCountModels(
            IReadOnlyList<MasterDataId> mstEventIds,
            IReadOnlyList<(MasterDataId mstEventId, MasterDataId mstMissionId)> mstEventIdAndMstMissionIdPairs,
            EventMissionAchievementResultModel eventAchievementModel)
        {
            var receivedMissionCountDictionary = mstEventIdAndMstMissionIdPairs
                .GroupBy(x => x.mstEventId)
                .ToDictionary(g => g.Key, g => g.Count());
            
            var receivableMissionCountDictionary = ReceivableMissionCountCalculator.GetReceivableMissionEventCountDictionary(
                eventAchievementModel);
            
            return mstEventIds.Select(mstEventId =>
                {
                    var receivedMissionCount = receivedMissionCountDictionary.GetValueOrDefault(mstEventId, 0);
                    var receivableMissionCount = receivableMissionCountDictionary.GetValueOrDefault(mstEventId, 0);
                    var unreceivedMissionEventCount = Math.Max(0, receivableMissionCount - receivedMissionCount);

                    return new MissionEventRewardCountModel(
                        mstEventId,
                        new UnreceivedMissionRewardCount(unreceivedMissionEventCount));
                })
                .ToList();
        }

        void ApplyUpdatedFetchModel(
            MissionBulkReceiveRewardResultModel receiveMissionRewardModel, 
            IReadOnlyList<MissionEventRewardCountModel> rewardCountModels)
        {
            var updatedFetchModel = UpdateFetchModel(
                receiveMissionRewardModel.UserParameterModel,
                rewardCountModels);
            
            var updatedFetchOtherModel = UpdateFetchOtherModel(receiveMissionRewardModel);
            GameManagement.SaveGameUpdateAndFetch(updatedFetchModel, updatedFetchOtherModel);
        }

        GameFetchModel UpdateFetchModel(
            UserParameterModel userParameterModel,
            IReadOnlyList<MissionEventRewardCountModel> rewardCountModels)
        {
            var fetchModel = GameRepository.GetGameFetch();
            
            // BadgeModelの更新
            var updatedBadgeModel = GetUpdatedBadgeModel(
                fetchModel.BadgeModel,
                rewardCountModels);

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

        IReadOnlyList<MasterDataId> GetReceivableEventMissionIds(MissionType missionType, IReadOnlyList<MasterDataId> mstEventIds)
        {
            var cacheModels = mstEventIds.Select(mstId => MissionEventCacheRepository.GetMissionEventModelOrDefault(mstId));

            switch (missionType)
            {
                case MissionType.Event:
                    return cacheModels.SelectMany(m => m.UserMissionEventModels)
                        .Where(model => model.IsCleared && !model.IsReceivedReward)
                        .Select(model => model.MstMissionEventId).ToList();
                default:
                    throw new Exception("Invalid mission type");
            }
        }

        BadgeModel GetUpdatedBadgeModel(
            BadgeModel beforeBadgeModel,
            IReadOnlyList<MissionEventRewardCountModel> rewardCountModels)
        {
            var updatedBadgeModel = beforeBadgeModel with
            {
                UnreceivedMissionEventRewardCounts = beforeBadgeModel.UnreceivedMissionEventRewardCounts.Update(rewardCountModels)
            };
            
            return updatedBadgeModel;
        }
    }
}
