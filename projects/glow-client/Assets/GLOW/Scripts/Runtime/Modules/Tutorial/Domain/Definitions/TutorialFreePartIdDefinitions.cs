using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Modules.Tutorial.Domain.Definitions
{
    public class TutorialFreePartIdDefinitions
    {
        public static TutorialFunctionName ReleaseHardStage { get; } = new("ReleaseHardStage");                        // ハード開放
        public static TutorialFunctionName ReleaseEventQuest { get; } = new("ReleaseEventQuest");                      // いいジャン祭開放
        public static TutorialFunctionName ReleasePvp { get; } = new("ReleasePvp");                                    // ランクマッチ開放
        public static TutorialFunctionName ReleaseAdventBattle { get; } = new ("ReleaseAdventBattle");                 // 降臨バトル開放
        public static TutorialFunctionName OutpostEnhance { get; } = new("OutpostEnhance"); // ゲート強化チュートリアル
        public static TutorialFunctionName IdleIncentive { get; } = new("IdleIncentive"); // 放置報酬チュートリアル
        public static TutorialFunctionName SpecialUnit { get; } = new("SpecialUnit"); // スペシャルユニット
        public static TutorialFunctionName ArtworkFragment { get; } = new("ArtworkFragment"); // 原画のかけら獲得
        public static TutorialFunctionName ReleaseEnhanceQuest { get; } = new("ReleaseEnhanceQuest"); // コイン獲得クエストチュートリアル
        public static TutorialFunctionName TransitEnhanceQuest { get; } = new("TransitEnhanceQuest"); // コイン獲得クエストの初遷移時
        public static TutorialFunctionName TransitOutpostEnhance { get; } = new("TransitOutpostEnhance"); // ゲート強化の初遷移時
        public static TutorialFunctionName TransitAdventBattle { get; } = new("TransitAdventBattle"); // 降臨バトルの初遷移時
        public static TutorialFunctionName TransitRaidAdventBattle { get; } = new("TransitRaidAdventBattle"); // 降臨バトル 協力スコア開催中の初遷移時
        public static TutorialFunctionName TransitPvp { get; } = new("TransitPvp"); // ランクマッチ開催中の初遷移時
    }
}
