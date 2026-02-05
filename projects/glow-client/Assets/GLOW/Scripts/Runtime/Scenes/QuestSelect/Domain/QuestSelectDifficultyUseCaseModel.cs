using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;

namespace GLOW.Scenes.QuestSelect.Domain
{
    public record QuestSelectDifficultyUseCaseModel(
        MasterDataId MstQuestId,
        Difficulty Difficulty,
        QuestDifficultyOpenStatus DifficultyOpenStatus,
        QuestDifficultyReleaseRequiredSentence ReleaseRequiredSentence,
        ArtworkFragmentNum GettableArtworkFragmentNum,
        ArtworkFragmentNum AcquiredArtworkFragmentNum)
    {
        public static QuestSelectDifficultyUseCaseModel Empty { get; } = new QuestSelectDifficultyUseCaseModel(
            MasterDataId.Empty,
            Difficulty.Normal,
            QuestDifficultyOpenStatus.NotRelease,
            QuestDifficultyReleaseRequiredSentence.CreateEmptySentence(),
            ArtworkFragmentNum.Empty,
            ArtworkFragmentNum.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
