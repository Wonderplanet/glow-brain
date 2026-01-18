using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Presentation.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public interface ISpeechBalloonTrackSpeechBalloon
    {
        void SetAnimationTime(SpeechBalloonAnimationTime time);
        void EndSpeech();
    }
}
