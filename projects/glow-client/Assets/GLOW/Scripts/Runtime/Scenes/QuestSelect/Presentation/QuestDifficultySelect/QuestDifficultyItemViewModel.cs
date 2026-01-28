using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;

namespace GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect
{
    public record QuestDifficultyItemViewModel(
        MasterDataId MstQuestId,
        Difficulty Difficulty,
        QuestDifficultyOpenStatus DifficultyOpenStatus,
        QuestDifficultyReleaseRequiredSentence ReleaseRequiredSentence,
        ArtworkFragmentNum GettableArtworkFragmentNum,
        ArtworkFragmentNum AcquiredArtworkFragmentNum)
    {
        public static QuestDifficultyItemViewModel Empty { get; } = new (
            MasterDataId.Empty, 
            Difficulty.Normal,
            QuestDifficultyOpenStatus.NotRelease,
            QuestDifficultyReleaseRequiredSentence.Empty,
            ArtworkFragmentNum.Empty,
            ArtworkFragmentNum.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}