using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Modules.TutorialTipDialog.Domain.Definitions
{
    public static class HelpDialogIdDefinitions
    {
        public static TutorialFunctionName AdventBattle { get; } = new ("TransitAdventBattle");
        public static TutorialFunctionName RaidAdventBattle { get; } = new ("TransitRaidAdventBattle"); 
        public static TutorialFunctionName EnhanceQuest { get; } = new ("TransitEnhanceQuest"); 
        public static TutorialFunctionName Pvp { get; } = new ("ReleasePvp");
        public static TutorialFunctionName PvpTop { get; } = new ("TransitPvp");
    }
}
