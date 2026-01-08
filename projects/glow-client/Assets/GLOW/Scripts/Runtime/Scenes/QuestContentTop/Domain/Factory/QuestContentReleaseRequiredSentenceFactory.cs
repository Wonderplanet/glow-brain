using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.QuestContent;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.QuestContentTop.Domain.Factory
{
    public static class QuestContentReleaseRequiredSentenceFactory
    {
        public static QuestContentReleaseRequiredSentence Create(
            QuestName questName,
            Difficulty difficulty,
            StageNumber number)
        {
            return new QuestContentReleaseRequiredSentence(
                ZString.Format("{0}（{1}）\n{2}話クリアで開放",
                    questName.Value,
                    DifficultyToStringConverter.DifficultyToString(difficulty),
                    number.Value));
        }

        public static QuestContentReleaseRequiredSentence Create(
            UserLevel releaseRequiredUserLevel)
        {
            return new QuestContentReleaseRequiredSentence(
                ZString.Format("リーダーLv. {0}で開放", releaseRequiredUserLevel.Value));
        }
    }
}
