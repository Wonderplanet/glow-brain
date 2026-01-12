using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.ViewModels;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views
{
    public interface IEncyclopediaArtworkDetailViewDelegate
    {
        void OnViewDidLoad();
        void OnSwitchOutpostArtworkButtonTapped();
        void OnSelectArtworkExpand(MasterDataId mstArtworkId);
        void OnSwitchArtwork(MasterDataId mstArtworkId);
        void OnSelectFragmentDropQuest(EncyclopediaArtworkFragmentListCellViewModel viewModel);
        void OnBackButtonTapped();
    }
}
