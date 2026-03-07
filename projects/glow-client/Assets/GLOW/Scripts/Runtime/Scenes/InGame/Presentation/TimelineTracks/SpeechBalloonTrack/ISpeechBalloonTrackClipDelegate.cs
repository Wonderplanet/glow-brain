using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public interface ISpeechBalloonTrackClipDelegate
    {
        ISpeechBalloonTrackSpeechBalloon GenerateSpeechBalloon(
            AutoPlayerSequenceElementId speaker,
            SpeechBalloonText text,
            SpeechBalloonAnimationTime timeOffset);
    }
}
