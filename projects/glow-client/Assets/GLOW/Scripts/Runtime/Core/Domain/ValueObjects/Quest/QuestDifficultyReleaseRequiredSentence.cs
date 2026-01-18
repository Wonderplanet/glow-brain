using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Domain.ValueObjects.Quest
{
    public record QuestDifficultyReleaseRequiredSentence(string Value)
    {
        public static QuestDifficultyReleaseRequiredSentence Empty { get; } = new (string.Empty);
        
        public static QuestDifficultyReleaseRequiredSentence CreateFormattedSentence(QuestName questName, Difficulty difficulty, StageNumber number)
        {
            return new QuestDifficultyReleaseRequiredSentence(ZString.Format("{0}（{1}）\n{2}話クリアで開放", questName.Value, DifficultyToStringConverter.DifficultyToString(difficulty), number.Value));
        }
        public static QuestDifficultyReleaseRequiredSentence CreateEmptySentence()
        {
            return new QuestDifficultyReleaseRequiredSentence("未解放の難易度です。");
        }

        public static QuestDifficultyReleaseRequiredSentence GenerateNoStagesSentence()
        {
            return new QuestDifficultyReleaseRequiredSentence("対象となるステージはありません。");
        }

    }

    public static class DifficultyToStringConverter
    {
        public static string DifficultyToString(Difficulty difficulty)
        {
            return difficulty switch
            {
                Difficulty.Normal => "ノーマル",
                Difficulty.Hard => "ハード",
                Difficulty.Extra => "エクストラ",
                _ => throw new System.ArgumentOutOfRangeException(nameof(difficulty), difficulty, null)
            };
        }
    }
}
