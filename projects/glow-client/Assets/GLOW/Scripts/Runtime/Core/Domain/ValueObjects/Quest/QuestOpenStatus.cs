namespace GLOW.Core.Domain.ValueObjects.Quest
{
    public enum QuestOpenStatus
    {
        Released,
        NoClearRequiredStage,// 開放条件のステージが未クリア(今イベントにのみ使ってる)
        NotOpenQuest,//クエスト開催期間外
        QuestEnded, // クエスト終了
        NoPlayableStage // 回数上限で遊べるステージが無い
    }
}
