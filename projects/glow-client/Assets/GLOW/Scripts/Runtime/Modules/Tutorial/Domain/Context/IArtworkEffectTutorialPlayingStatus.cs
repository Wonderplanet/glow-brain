using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Modules.Tutorial.Domain.Context
{
    public interface IArtworkEffectTutorialPlayingStatus
    {
        PlayingTutorialSequenceFlag IsPlayingTutorialSequence { get; }
    }
}