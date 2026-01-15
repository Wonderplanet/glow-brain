using GLOW.Core.Domain.ValueObjects.QuestContent;
using GLOW.Scenes.QuestContentTop.Domain.enums;

namespace GLOW.Scenes.QuestContentTop.Domain.UseCaseModel
{
    public record QuestContentOpeningStatusModel(
        QuestContentOpeningStatusAtTimeType OpeningStatusAtTimeType,
        QuestContentOpeningStatusAtUserStatus OpeningStatusAtUserStatus,
        QuestContentReleaseRequiredSentence QuestContentReleaseRequiredSentence)
    {
        public static QuestContentOpeningStatusModel Empty { get; } = new(
            QuestContentOpeningStatusAtTimeType.OutOfLimit,
            QuestContentOpeningStatusAtUserStatus.None,
            QuestContentReleaseRequiredSentence.Empty
        );
        
        public bool IsOpening()
        {
            return OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.Opening &&
                   OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.None;
        }
    }
}
