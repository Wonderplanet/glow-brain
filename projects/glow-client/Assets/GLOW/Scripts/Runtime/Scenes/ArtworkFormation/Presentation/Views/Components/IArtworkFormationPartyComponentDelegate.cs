using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkFormation.Presentation.Views.Components
{
    public interface IArtworkFormationPartyComponentDelegate
    {
        void OnPartyCellTapped(MasterDataId mstArtworkId);
    }
}

