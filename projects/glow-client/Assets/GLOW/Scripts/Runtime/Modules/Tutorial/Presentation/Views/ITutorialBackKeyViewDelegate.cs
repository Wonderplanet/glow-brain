using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Modules.Tutorial.Presentation.Views
{
    public interface ITutorialBackKeyViewDelegate
    {
        PlayingTutorialSequenceFlag IsPlayingTutorial();
    }
}