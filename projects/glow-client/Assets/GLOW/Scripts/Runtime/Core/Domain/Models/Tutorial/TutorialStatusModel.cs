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
        
        // 初期状態
        public bool IsInitial()
        {
            return this == TutorialSequenceIdDefinitions.TutorialStart;
        }
        
        public bool ShouldSetName()
        {
            return this == TutorialSequenceIdDefinitions.NewTutorialStart;
        }
        
        public bool IsIntroduction()
        {
            return this == TutorialSequenceIdDefinitions.NewStartInGame;
        }
        
        public bool ShouldDownloadOnLogin()
        {
            // チュートリアルステージクリアまではログイン時にダウンロードしない
            return this != TutorialSequenceIdDefinitions.TutorialStart &&
                   this != TutorialSequenceIdDefinitions.TutorialStartIntroduction &&
                   this != TutorialSequenceIdDefinitions.NewTutorialStart &&
                   this != TutorialSequenceIdDefinitions.NewStartInGame;
        }
        
        public bool IsStartMainPart1()
        {
            return this == TutorialSequenceIdDefinitions.NewStartMainPart;
        }

        public bool IsMainPart1()
        {
            return this == TutorialSequenceIdDefinitions.NewStartMainPart ||
                   this == TutorialSequenceIdDefinitions.NewGachaConfirmed ||
                   this == TutorialSequenceIdDefinitions.NewUnitEnhanced ||
                   this == TutorialSequenceIdDefinitions.NewPartyFormationConfirmed;
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

        public bool IsGachaConfirmed()
        {
            return this == TutorialSequenceIdDefinitions.NewGachaConfirmed;
        }
        
        public bool IsUnitEnhanced()
        {
            return this == TutorialSequenceIdDefinitions.NewUnitEnhanced;
        }
        
        public bool IsPartyFormationConfirmed()
        {
            return this == TutorialSequenceIdDefinitions.NewPartyFormationConfirmed;
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
            // イントロステージクリア後のStartMainPartステータスの場合は遷移をスキップする
            return this == TutorialSequenceIdDefinitions.NewStartMainPart;
        }
        
        // 新規チュートリアルステータスの場合
        public bool IsRenewalTutorialStatus()
        {
            return this == TutorialSequenceIdDefinitions.NewTutorialStart ||
                   this == TutorialSequenceIdDefinitions.NewStartInGame ||
                   this == TutorialSequenceIdDefinitions.NewStartMainPart ||
                   this == TutorialSequenceIdDefinitions.NewGachaConfirmed ||
                   this == TutorialSequenceIdDefinitions.NewUnitEnhanced ||
                   this == TutorialSequenceIdDefinitions.NewPartyFormationConfirmed;
        }
        
        // チュートリアル刷新用 ガシャ以前の場合
        public bool IsBeforeGacha()
        {
            return IsEmpty()
                   || this == TutorialSequenceIdDefinitions.TutorialStartIntroduction
                   || this == TutorialSequenceIdDefinitions.TutorialMainPart_start;
        }
    }
}
