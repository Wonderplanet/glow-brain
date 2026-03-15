using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkList.Presentation.Views
{
    public interface IArtworkListViewDelegate
    {
        void OnViewWillAppear();
        void OnListCellTapped(MasterDataId mstArtworkId);
        void OnSortAndFilterButtonTapped();
        void OnSortButtonTapped();
    }
}

