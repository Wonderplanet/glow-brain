using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Modules.Tutorial.Domain.Evaluator;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class EnabledBackKeyInTutorialUseCase
    {
        [Inject] ITutorialPlayingStatus TutorialPlayingStatus { get; }
        [Inject] IFreePartTutorialPlayingStatus FreePartTutorialPlayingStatus { get; }
        [Inject] IPvpTutorialPlayingStatus PvpTutorialPlayingStatus { get; }
        
        public PlayingTutorialSequenceFlag IsPlayingTutorial()
        {
            var isPlayingTutorial = PlayingTutorialSequenceEvaluator.IsPlayingTutorial(
                TutorialPlayingStatus, 
                FreePartTutorialPlayingStatus,
                PvpTutorialPlayingStatus);
            
            return isPlayingTutorial;
        }
    }
}