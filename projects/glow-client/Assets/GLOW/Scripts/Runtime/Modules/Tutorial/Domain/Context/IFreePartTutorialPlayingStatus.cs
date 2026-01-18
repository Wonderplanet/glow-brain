using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Modules.Tutorial.Domain.Context
{
    public interface IFreePartTutorialPlayingStatus
    {
        PlayingTutorialSequenceFlag IsPlayingTutorialSequence { get; }
    }
}