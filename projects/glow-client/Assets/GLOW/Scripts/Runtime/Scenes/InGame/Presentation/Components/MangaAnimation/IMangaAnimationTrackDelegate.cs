using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.Components.MangaAnimation
{
    public interface IMangaAnimationTrackDelegate
    {
        IMangaAnimationSpeechBalloon GenerateSpeechBalloon(
            AutoPlayerSequenceElementId speaker,
            SpeechBalloonText text,
            SpeechBalloonAnimationTime timeOffset);

        float GetCurrentKomaScrollPosition();
        float GetKomaScrollPosition(AutoPlayerSequenceElementId target);
        void SetKomaScrollPosition(float position);

        KomaId GetKomaId(AutoPlayerSequenceElementId target);
        void SetKomaZoomRate(KomaId komaId, AutoPlayerSequenceElementId target, float zoomRate);
    }
}
