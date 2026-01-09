using System.Linq;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.QuestSelect.Domain;
using GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect;
using UnityEngine;

namespace GLOW.Scenes.QuestSelect.Presentation
{
    public static class QuestSelectViewModelTranslator
    {
        public static QuestSelectViewModel CreateQuestSelectViewModel(QuestSelectUseCaseModel useCaseModel)
        {
            var viewModels = useCaseModel.Items
                .Select(CreateQuestSelectContentViewModel)
                .ToList();
            
            var selectedIndex = viewModels
                .FindIndex(i => i.MstGroupQuestId == useCaseModel.CurrentSelectMstQuestGroupId);
            
            selectedIndex = Mathf.Max(selectedIndex, 0);

            return new QuestSelectViewModel(new CollectionViewCurrentIndex(selectedIndex), viewModels);
        }

        static QuestSelectContentViewModel CreateQuestSelectContentViewModel(QuestSelectContentUseCaseModel useCaseModel)
        {
            return new QuestSelectContentViewModel(
                useCaseModel.GroupId,
                useCaseModel.QuestName,
                useCaseModel.Difficulty,
                QuestImageAssetPath.GetQuestImagePath(useCaseModel.AssetKey.Value),
                useCaseModel.FlavorText,
                useCaseModel.Status,
                useCaseModel.NewQuestExists,
                GetQuestUnlockRequirementDescription(useCaseModel.RequiredSentenceStatus),
                useCaseModel.DifficultyItems.Select(CreateQuestDifficultyItemViewModel).ToList(),
                useCaseModel.NormalCampaignModels.Select(CampaignViewModelTranslator.ToCampaignViewModel).ToList(),
                useCaseModel.HardCampaignModels.Select(CampaignViewModelTranslator.ToCampaignViewModel).ToList(),
                useCaseModel.ExtraCampaignModels.Select(CampaignViewModelTranslator.ToCampaignViewModel).ToList());
        }

        static QuestDifficultyItemViewModel CreateQuestDifficultyItemViewModel(QuestSelectDifficultyUseCaseModel model)
        {
            return new QuestDifficultyItemViewModel(
                model.MstQuestId,
                model.Difficulty,
                model.DifficultyOpenStatus,
                model.ReleaseRequiredSentence,
                model.GettableArtworkFragmentNum,
                model.AcquiredArtworkFragmentNum);
        }

        static QuestUnlockRequirementDescription GetQuestUnlockRequirementDescription(
            QuestSelectContentUnlockDescriptionStatus status)
        {

            if (!status.IsOpened)
            {
                return QuestUnlockRequirementDescription.CreateOpenLimitSentence(status.RemainingTimeSpan.Value);
            }
            else if (!status.RequiredQuestName.IsEmpty())
            {
                return QuestUnlockRequirementDescription.CreateRequiredSentence(status.RequiredQuestName.Value);
            }
            else
            {
                return QuestUnlockRequirementDescription.CreateEmptySentence("");
            }
        }
    }
}

