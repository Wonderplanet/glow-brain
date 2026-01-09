using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Scenes.EventQuestSelect.Domain.Evaluator;
using GLOW.Scenes.QuestSelect.Domain;
using Zenject;

namespace GLOW.Scenes.EventQuestSelect.Domain.Factory
{
    public class EventQuestListUseCaseElementModelFactory : IEventQuestListUseCaseElementModelFactory
    {
        [Inject] IQuestOpenStatusEvaluator QuestOpenStatusEvaluator { get; }
        [Inject] INewQuestEvaluator NewQuestEvaluator { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IReleaseRequiredMstQuestFactory ReleaseRequiredMstQuestFactory { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        static IReadOnlyList<Difficulty> OrderEnum =
            new List<Difficulty>() { Difficulty.Normal, Difficulty.Hard, Difficulty.Extra };

        public IReadOnlyList<EventQuestListUseCaseElementModel> Create(MasterDataId mstEventId)
        {
            var targetQuests = MstQuestDataRepository.GetMstQuestModelsFromEvent(mstEventId);

            return targetQuests
                .GroupBy(t => t.GroupId)
                .Select(s => CreateElement(s.ToList()))
                .ToList();
        }

        EventQuestListUseCaseElementModel CreateElement(
            IReadOnlyList<MstQuestModel> groupedMstQuestModels)
        {
            // グループの最初だけ出すようにする(未処理だとノーマル、ハードなど全部出る)
            var mstQuestModel = groupedMstQuestModels.MinBy(m => OrderEnum.IndexOf(m.Difficulty));

            // 基本はFirstで大丈夫。文言の整形にのみ複数必要
            var openStatuses = QuestOpenStatusEvaluator.EvaluateGetAllStatus(mstQuestModel);

            var releaseSentenceStatus = CreateEventQuestUnlockRequirementDescriptionStatus(
                mstQuestModel.StartDate,
                openStatuses,
                ReleaseRequiredMstQuestFactory.Create(mstQuestModel).Name,
                GetReleaseRequiredStageNumber(mstQuestModel)
                );

            return new EventQuestListUseCaseElementModel(
                mstQuestModel.GroupId,
                NewQuestEvaluator.IsNewQuestAtEvent(openStatuses[0], mstQuestModel),
                openStatuses,
                mstQuestModel.Name,
                EventQuestSelectElementAssetPath.FromAssetKey(mstQuestModel.AssetKey),
                releaseSentenceStatus
            );
        }


        StageNumber GetReleaseRequiredStageNumber(MstQuestModel mstQuestModel)
        {
            var mstStageModels = MstStageDataRepository.GetMstStagesFromMstQuestId(mstQuestModel.Id);
            var targetMstStageModel = mstStageModels.MinBy(m => m.StageNumber.Value);
            if(targetMstStageModel.ReleaseRequiredMstStageId.IsEmpty()) return StageNumber.Empty;

            return MstStageDataRepository.GetMstStage(targetMstStageModel.ReleaseRequiredMstStageId).StageNumber;
        }

        EventQuestUnlockRequirementDescriptionStatus CreateEventQuestUnlockRequirementDescriptionStatus(
            UnlimitedCalculableDateTimeOffset startDate,
            IReadOnlyList<QuestOpenStatus> openStatuses,
            QuestName releaseRequiredQuestName,
            StageNumber stageNumber)
        {
            var remainingTime = startDate - TimeProvider.Now;
            return new EventQuestUnlockRequirementDescriptionStatus(
                new RemainingTimeSpan(remainingTime),
                openStatuses,
                releaseRequiredQuestName,
                stageNumber);
        }

    }
}
