using System.Collections.Generic;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.Models;

namespace GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel
{
    public record ArtworkAcquisitionRouteUseCaseModel(
        IReadOnlyList<EncyclopediaArtworkFragmentListCellModel> FragmentListCellModels,
        IReadOnlyList<ArtworkAcquisitionRouteCellModel> AcquisitionRoutes);
}
