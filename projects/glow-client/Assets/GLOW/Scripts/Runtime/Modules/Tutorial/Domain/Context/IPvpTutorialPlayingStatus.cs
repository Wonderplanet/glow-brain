using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Modules.Tutorial.Domain.Context
{
    public interface IPvpTutorialPlayingStatus
    {
        PlayingTutorialSequenceFlag IsPlayingTutorialSequence { get; }
    }
}