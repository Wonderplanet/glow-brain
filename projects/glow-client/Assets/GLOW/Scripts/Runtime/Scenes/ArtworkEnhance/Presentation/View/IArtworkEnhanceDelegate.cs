using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public interface IArtworkEnhanceDelegate
    {
        void OnViewDidLoad();
        void OnItemIconTapped(PlayerResourceIconViewModel viewModel);
        void OnSwitchArtwork(MasterDataId mstArtworkId);
        void OnEnhanceButtonTapped(MasterDataId mstArtworkId);
        void OnInfoButtonTapped(MasterDataId mstArtworkId);
        void OnBackButtonTapped();
    }
}
