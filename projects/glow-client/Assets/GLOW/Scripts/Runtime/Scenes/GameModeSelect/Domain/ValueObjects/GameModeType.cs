using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.GameModeSelect.Domain
{
    public enum GameModeType
    {
        MeinQuest,
        Event,
        Reprint
    }
    public static class QuestTypeExtensions
    {
        public static GameModeType ToGameModeType(this QuestType type)
        {
            return type switch
            {
                QuestType.Normal => GameModeType.MeinQuest,
                QuestType.Event => GameModeType.Event,
                _ => GameModeType.MeinQuest
            };
        }
    }
    public static class GameModeTypeExtensions
    {
        public static QuestType ToQuestType(this GameModeType type)
        {
            return type switch
            {
                GameModeType.MeinQuest => QuestType.Normal,
                GameModeType.Event => QuestType.Event,
                GameModeType.Reprint => QuestType.Event,
                _ => QuestType.Normal
            };
        }
    }
}
