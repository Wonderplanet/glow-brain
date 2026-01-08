using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Modules.Tutorial.Domain.Definitions
{
    public static class TutorialSequenceIdDefinitions
    {
        public static TutorialStatusModel TutorialStart { get; } = new (new TutorialFunctionName(""));
        public static TutorialStatusModel TutorialStartIntroduction { get; } = new (new TutorialFunctionName("TutorialStartIntroduction"));
        public static TutorialStatusModel TutorialMainPart_start { get; } = new (new TutorialFunctionName("StartMainPart1"));
        public static TutorialStatusModel TutorialMainPart_gachaConfirmed { get; } = new (new TutorialFunctionName("GachaConfirmed"));
        public static TutorialStatusModel TutorialMainPart_setPartyFormation { get; } = new (new TutorialFunctionName("SetPartyFormation"));
        public static TutorialStatusModel TutorialMainPart_startInGame_1 { get; } = new (new TutorialFunctionName("StartInGame1"));
        public static TutorialStatusModel TutorialMainPart_startMainPart2 { get; } = new (new TutorialFunctionName("StartMainPart2"));
        public static TutorialStatusModel TutorialMainPart_enhancedUnit { get; } = new (new TutorialFunctionName("EnhancedUnit"));
        public static TutorialStatusModel TutorialMainPart_startInGame_2 { get; } = new (new TutorialFunctionName("StartInGame2"));
        public static TutorialStatusModel TutorialMainPart_startMainPart3 { get; } = new (new TutorialFunctionName("StartMainPart3"));
        public static TutorialStatusModel TutorialMainPart_completeTutorial { get; } = new (new TutorialFunctionName("MainPartCompleted"));
    }
} 