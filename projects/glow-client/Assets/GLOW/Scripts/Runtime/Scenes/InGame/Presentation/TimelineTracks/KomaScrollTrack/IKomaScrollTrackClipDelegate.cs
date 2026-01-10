using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public interface IKomaScrollTrackClipDelegate
    {
        float GetCurrentKomaScrollPosition();
        float GetKomaScrollPosition(AutoPlayerSequenceElementId target);
        void SetKomaScrollPosition(float position);
    }
}
