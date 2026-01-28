using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Modules.Tutorial.Presentation.Views;
using Zenject;

namespace GLOW.Modules.Tutorial.Presentation.Presenters
{
    public class TutorialBackKeyHandler : ITutorialBackKeyViewDelegate
    {
        [Inject] EnabledBackKeyInTutorialUseCase EnabledBackKeyInTutorialUseCase { get; }

        public PlayingTutorialSequenceFlag IsPlayingTutorial()
        {
            return EnabledBackKeyInTutorialUseCase.IsPlayingTutorial();
        }
        
    }
}