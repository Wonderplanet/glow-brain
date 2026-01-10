using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Extensions;
using GLOW.Scenes.EventQuestSelect.Domain.Evaluator;
using Zenject;

namespace GLOW.Scenes.QuestSelect.Domain
{
    public class QuestSelectUseCaseModelItemFactory : IQuestSelectUseCaseModelItemFactory
    {
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IQuestDifficultyUseCaseModelItemFactory QuestDifficultyUseCaseModelItemFactory { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] INewQuestEvaluator NewQuestEvaluator { get; }
        [Inject] IQuestOpenStatusEvaluator QuestOpenStatusEvaluator { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }

        QuestSelectContentUseCaseModel IQuestSelectUseCaseModelItemFactory.CreateQuestSelectContentUseCaseModel(
            MstQuestModel targetMstQuestModel,
            MstStageModel questReleaseCheckSampleModel)
        {
            var difficultyItems =
                QuestDifficultyUseCaseModelItemFactory.CreateDifficultyItems(targetMstQuestModel);

            var openStatus = QuestOpenStatusEvaluator.Evaluate(targetMstQuestModel);

            var targetAllDifficultyQuests = MstQuestDataRepository.GetMstQuestModels()
                .Where(q => q.GroupId == targetMstQuestModel.GroupId)
                .ToList();

            return new QuestSelectContentUseCaseModel(
                targetMstQuestModel.GroupId,
                targetMstQuestModel.Name,
                targetMstQuestModel.Difficulty,
                targetMstQuestModel.AssetKey,
                targetMstQuestModel.QuestFlavorText,
                CreateQuestSelectContentUnlockDescriptionStatus(targetMstQuestModel, questReleaseCheckSampleModel.ReleaseRequiredMstStageId),
                openStatus,
                NewQuestEvaluator.IsNewQuest(openStatus, targetMstQuestModel),
                difficultyItems,
                GetCampaignModels(targetAllDifficultyQuests, Difficulty.Normal),
                GetCampaignModels(targetAllDifficultyQuests, Difficulty.Hard),
                GetCampaignModels(targetAllDifficultyQuests, Difficulty.Extra));
        }

        QuestSelectContentUnlockDescriptionStatus CreateQuestSelectContentUnlockDescriptionStatus(MstQuestModel mstQuestModel, MasterDataId releaseRequiredMstStageId)
        {
            var isOpened = mstQuestModel.StartDate <= TimeProvider.Now && TimeProvider.Now <= mstQuestModel.EndDate;
            var remainingTime = mstQuestModel.StartDate - TimeProvider.Now;
            var requiredQuestName =TargetQuestName(releaseRequiredMstStageId);
            return new QuestSelectContentUnlockDescriptionStatus(isOpened, new RemainingTimeSpan(remainingTime), requiredQuestName);
        }

        QuestName TargetQuestName(MasterDataId releaseRequiredMstStageId)
        {
            var questReleaseRequiredMstStage = MstStageDataRepository.GetMstStageFirstOrDefault(releaseRequiredMstStageId);

            if (questReleaseRequiredMstStage.IsEmpty())
            {
                return QuestName.Empty;
            }

            var questReleaseRequiredMstQuest =
                MstQuestDataRepository.GetMstQuestModel(questReleaseRequiredMstStage.MstQuestId);
            return questReleaseRequiredMstQuest.Name;
        }

        List<CampaignModel> GetCampaignModels(List<MstQuestModel> targetQuestModels, Difficulty difficulty)
        {
            var targetMstQuestModel = targetQuestModels
                .FirstOrDefault(m => m.Difficulty == difficulty, MstQuestModel.Empty);

            var campaignTargetType = targetMstQuestModel.QuestType == QuestType.Event ?
                CampaignTargetType.EventQuest :
                CampaignTargetType.NormalQuest;
            return CampaignModelFactory.CreateCampaignModels(
                targetMstQuestModel.Id,
                campaignTargetType,
                CampaignTargetIdType.Quest,
                targetMstQuestModel.Difficulty);
        }
    }
}
