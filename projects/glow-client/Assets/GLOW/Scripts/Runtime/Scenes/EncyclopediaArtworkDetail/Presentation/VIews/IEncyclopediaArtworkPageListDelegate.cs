using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views
{
    public interface IEncyclopediaArtworkPageListDelegate
    {
        void SwitchArtwork(MasterDataId mstArtworkId);
        void WillTransitionTo();
        void DidFinishAnimating(bool finished, bool transitionCompleted);
    }
}
