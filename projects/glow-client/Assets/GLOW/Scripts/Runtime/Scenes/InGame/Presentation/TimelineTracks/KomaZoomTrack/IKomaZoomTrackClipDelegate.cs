using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public interface IKomaZoomTrackClipDelegate
    {
        KomaId GetKomaId(AutoPlayerSequenceElementId target);
        void SetKomaZoomRate(KomaId komaId, AutoPlayerSequenceElementId target, float zoomRate);
    }
}
