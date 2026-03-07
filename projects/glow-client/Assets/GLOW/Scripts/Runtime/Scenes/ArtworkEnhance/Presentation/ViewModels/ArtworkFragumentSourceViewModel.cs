using System.Collections.Generic;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels
{
    public record ArtworkAcquisitionRouteViewModel(
        IReadOnlyList<EncyclopediaArtworkFragmentListCellViewModel> FragmentListCellViewModels,
        IReadOnlyList<ArtworkAcquisitionRouteCellViewModel> AcquisitionRoutes);
}
