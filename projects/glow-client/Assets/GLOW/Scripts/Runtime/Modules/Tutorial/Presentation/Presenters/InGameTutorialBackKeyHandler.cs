using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Modules.Tutorial.Presentation.Views;
using Zenject;

namespace GLOW.Modules.Tutorial.Presentation.Presenters
{
    public class InGameTutorialBackKeyHandler : IInGameTutorialBackKeyViewDelegate
    {
        [Inject] CheckTutorialCompletedUseCase CheckTutorialCompletedUseCase { get; }

        public PlayingTutorialSequenceFlag IsPlayingTutorial()
        {
            // チュートリアルが未完了であればチュートリアル進行中
            if (!CheckTutorialCompletedUseCase.CheckTutorialCompleted())
            {
                return PlayingTutorialSequenceFlag.True;
            }
            
            return PlayingTutorialSequenceFlag.False;
        }
    }
}