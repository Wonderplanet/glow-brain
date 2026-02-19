using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Scenes.EventQuestTop.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public class EventQuestTopUseCase
    {
        [Inject] IMstEventDataRepository EventDataRepository { get; }
        [Inject] IMstQuestDataRepository QuestDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IQuestStageReleaseAnimationRepository QuestStageReleaseAnimationRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        [Inject] IEventQuestTopUseCaseElementModelFactory EventQuestTopUseCaseElementModelFactory { get; }
        [Inject] IArtworkFragmentStatusFactory ArtworkFragmentStatusFactory { get; }
        [Inject] IShowStageReleaseAnimationFactory ShowStageReleaseAnimationFactory { get; }
        [Inject] IEventQuestTopUnitUseCaseModelFactory EventQuestTopUnitUseCaseModelFactory { get; }
        [Inject] IEventInitialSelectStageFactory EventInitialSelectStageFactory { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }
        [Inject] IMstBoxGachaDataRepository MstBoxGachaDataRepository { get; }

        public EventQuestTopUseCaseModel UpdateAndGetModel(MasterDataId mstQuestGroupId)
        {
            var mstQuestModels = QuestDataRepository.GetMstQuestModelsByQuestGroup(mstQuestGroupId);
            var mstQuestModel = mstQuestModels.First();
            var latestEndMstQuestModel = mstQuestModels.MaxBy(m => m.EndDate.Value) ?? MstQuestModel.Empty;

            var mstEventModel = EventDataRepository.GetEvent(mstQuestModel.MstEventId);
            var stages = EventQuestTopUseCaseElementModelFactory.Create(mstQuestGroupId);

            var artworkFragmentModel = ArtworkFragmentStatusFactory.Create(mstQuestModels);

            var showReleaseAnimationStatus = QuestStageReleaseAnimationRepository.GetForEventStageSelect();
            var shouldShowReleaseAnimation =
                ShowStageReleaseAnimationFactory.Create(showReleaseAnimationStatus.NewReleaseMstStageId);
            var initialSelectStageMstStageId = EventInitialSelectStageFactory.Create(
                mstQuestGroupId,
                stages,
                shouldShowReleaseAnimation);
            var initialSelectStage = MstStageDataRepository.GetMstStage(initialSelectStageMstStageId);
            var initialSelectQuestModel =
                QuestDataRepository.GetMstQuestModelFirstOrDefault(initialSelectStage.MstQuestId);

            // もし開放演出がある状態で別のクエスト選択したときはEmptyにする
            if(shouldShowReleaseAnimation.ShouldShow &&
               initialSelectStageMstStageId != shouldShowReleaseAnimation.TargetMstStageId)
            {
                shouldShowReleaseAnimation = ShowStageReleaseAnimation.Empty;
            }
            ClearStageReleaseAnimationRepository(); //ここ副作用

            var campaignModels = CreateCampaignModels(
                initialSelectQuestModel.Id,
                initialSelectQuestModel.Difficulty);

            var releaseQuestNames = CreateNewReleaseQuestNameList(
                mstQuestModel.Id,
                showReleaseAnimationStatus.NewReleaseMstStageId);
            
            var boxGachaModel = MstBoxGachaDataRepository.GetMstBoxGachaModelByMstEventIdFirstOrDefault(
                mstQuestModel.MstEventId);

            return new EventQuestTopUseCaseModel(
                mstEventModel.Id,
                mstQuestGroupId,
                mstEventModel.Name,
                mstQuestModel.Name,
                mstQuestModel.CategoryName,
                EventQuestTopUnitUseCaseModelFactory.Create(mstQuestModel.Id),
                new RemainingTimeSpan(latestEndMstQuestModel.EndDate - TimeProvider.Now),
                latestEndMstQuestModel.EndDate,
                initialSelectStageMstStageId,
                stages,
                shouldShowReleaseAnimation,
                artworkFragmentModel.Gettable,
                artworkFragmentModel.Acquired,
                campaignModels,
                releaseQuestNames,
                boxGachaModel.Id);
        }

        List<CampaignModel> CreateCampaignModels(MasterDataId targetQuestId, Difficulty difficulty)
        {
            return CampaignModelFactory.CreateCampaignModels(
                targetQuestId,
                CampaignTargetType.EventQuest,
                CampaignTargetIdType.Quest,
                difficulty);
        }

        void ClearStageReleaseAnimationRepository()
        {
            QuestStageReleaseAnimationRepository.DeleteAtEvent();
        }

        IReadOnlyList<QuestName> CreateNewReleaseQuestNameList(MasterDataId currentMstQuestId, MasterDataId newReleaseMstStageId)
        {
            var list = new List<QuestName>();
            if (newReleaseMstStageId.IsEmpty()) return list;
            var mstStageModel = MstStageDataRepository.GetMstStage(newReleaseMstStageId);

            // 開放されたステージと同じ解放条件を持つクエストを取得
            var newReleaseMstStages = MstStageDataRepository.GetMstStages()
                .Where(mstStage => mstStage.ReleaseRequiredMstStageId == mstStageModel.ReleaseRequiredMstStageId)
                .ToList();
            foreach (var mstStage in newReleaseMstStages)
            {
                var mstQuestModel = QuestDataRepository.GetMstQuestModelFirstOrDefault(mstStage.MstQuestId);
                if(mstQuestModel.Id != currentMstQuestId
                   && CalculateTimeCalculator.IsValidTime(
                       TimeProvider.Now,
                       mstQuestModel.StartDate,
                       mstQuestModel.EndDate))
                {
                    list.Add(mstQuestModel.Name);
                }
            }

            return list
                .Distinct()
                .ToList();
        }
    }
}
