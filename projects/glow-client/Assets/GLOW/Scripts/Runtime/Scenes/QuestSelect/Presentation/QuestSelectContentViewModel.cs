using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;
using GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect;

namespace GLOW.Scenes.QuestSelect.Presentation
{
    public record QuestSelectContentViewModel(
        MasterDataId MstGroupQuestId,
        QuestName QuestName,
        Difficulty CurrentDifficulty,
        QuestImageAssetPath AssetPath,
        QuestFlavorText FlavorText,
        QuestOpenStatus OpenStatus,
        NewQuestFlag NewQuestExists,
        QuestUnlockRequirementDescription RequiredSentence,
        IReadOnlyList<QuestDifficultyItemViewModel> QuestDifficultyItemViewModels,
        IReadOnlyList<CampaignViewModel> NormalCampaignViewModels,
        IReadOnlyList<CampaignViewModel> HardCampaignViewModels,
        IReadOnlyList<CampaignViewModel> ExtraCampaignViewModels)
    {
        public static QuestSelectContentViewModel Empty { get; } = new QuestSelectContentViewModel(
            new MasterDataId(string.Empty),
            new QuestName(string.Empty),
            Difficulty.Normal,
            new QuestImageAssetPath(string.Empty),
            new QuestFlavorText(string.Empty),
            QuestOpenStatus.NotOpenQuest,
            NewQuestFlag.False,
            new QuestUnlockRequirementDescription(string.Empty),
            new List<QuestDifficultyItemViewModel>(),
            new List<CampaignViewModel>(),
            new List<CampaignViewModel>(),
            new List<CampaignViewModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public QuestSelectContentViewModel CopyWithUpdatedCurrentDifficulty(Difficulty difficulty)
        {
            return this with { CurrentDifficulty = difficulty };
        }

        public MasterDataId GetCurrentDifficultyQuestId()
        {
            var currentDifficultyViewModel = QuestDifficultyItemViewModels.FirstOrDefault(
                x => x.Difficulty == CurrentDifficulty,
                QuestDifficultyItemViewModel.Empty);

            return currentDifficultyViewModel.MstQuestId;
        }

        public ArtworkFragmentNum GetAcquiredArtworkFragmentNum()
        {
            var sum = QuestDifficultyItemViewModels.Sum(i => i.AcquiredArtworkFragmentNum.Value);
            return new ArtworkFragmentNum(sum);
        }

        public ArtworkFragmentNum GetGettableArtworkFragmentNum()
        {
            var sum = QuestDifficultyItemViewModels.Sum(i => i.GettableArtworkFragmentNum.Value);
            return new ArtworkFragmentNum(sum);
        }
    };
}
