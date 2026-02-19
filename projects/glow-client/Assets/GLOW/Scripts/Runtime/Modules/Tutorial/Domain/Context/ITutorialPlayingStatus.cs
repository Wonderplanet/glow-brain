using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Modules.Tutorial.Domain.Context
{
    public interface ITutorialPlayingStatus
    {
        PlayingTutorialSequenceFlag IsPlayingTutorialSequence { get; }
    }
}