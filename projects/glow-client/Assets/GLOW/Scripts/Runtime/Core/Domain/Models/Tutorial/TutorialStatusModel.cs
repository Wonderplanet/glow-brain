using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.Definitions;

namespace GLOW.Core.Domain.Models.Tutorial
{
    public record TutorialStatusModel(TutorialFunctionName TutorialFunctionName)
    {
        public static TutorialStatusModel Empty { get; } = new TutorialStatusModel(TutorialFunctionName.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public bool ShouldSetName()
        {
            return this == TutorialSequenceIdDefinitions.TutorialStart;
        }
        
        public bool IsIntroduction()
        {
            return this == TutorialSequenceIdDefinitions.TutorialStartIntroduction;
        }

        public bool IsMainPart1()
        {
            return this == TutorialSequenceIdDefinitions.TutorialMainPart_start ||
                   this == TutorialSequenceIdDefinitions.TutorialMainPart_gachaConfirmed ||
                   this == TutorialSequenceIdDefinitions.TutorialMainPart_setPartyFormation ||
                   this == TutorialSequenceIdDefinitions.TutorialMainPart_startInGame_1;
        }

        public bool IsMainPart2()
        {
            return this == TutorialSequenceIdDefinitions.TutorialMainPart_startMainPart2 ||
                   this == TutorialSequenceIdDefinitions.TutorialMainPart_enhancedUnit ||
                   this == TutorialSequenceIdDefinitions.TutorialMainPart_startInGame_2;
        }

        public bool IsMainPart3()
        {
            return this == TutorialSequenceIdDefinitions.TutorialMainPart_startMainPart3;
        }
        
        public bool IsCompleted()
        {
            return this == TutorialSequenceIdDefinitions.TutorialMainPart_completeTutorial;
        }
        
        public bool IsStartMainPart1()
        {
            return this == TutorialSequenceIdDefinitions.TutorialMainPart_start;
        }

        public bool IsGachaConfirmed()
        {
            return this == TutorialSequenceIdDefinitions.TutorialMainPart_gachaConfirmed;
        }

        public bool IsStartMainPart2()
        {
            return this == TutorialSequenceIdDefinitions.TutorialMainPart_startMainPart2;
        }
        
        public bool IsStartInGame1()
        {
            return this == TutorialSequenceIdDefinitions.TutorialMainPart_startInGame_1;
        }
        
        public bool IsStartInGame2()
        {
            return this == TutorialSequenceIdDefinitions.TutorialMainPart_startInGame_2;
        }

        public bool IsSkipTransitionStatus()
        {
            return this == TutorialSequenceIdDefinitions.TutorialStartIntroduction ||
                   this == TutorialSequenceIdDefinitions.TutorialMainPart_start ||
                   this == TutorialSequenceIdDefinitions.TutorialMainPart_startMainPart3;
        }
        
        public bool IsNormalStageDisplayed()
        {
            return IsMainPart3() || IsCompleted();
        }
    }
}
