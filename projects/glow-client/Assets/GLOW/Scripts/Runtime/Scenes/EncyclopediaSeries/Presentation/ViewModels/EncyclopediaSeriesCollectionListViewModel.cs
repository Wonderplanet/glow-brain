using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.ViewModels
{
    public record EncyclopediaSeriesCollectionListViewModel(
        IReadOnlyList<EncyclopediaArtworkListCellViewModel> ArtworkList,
        IReadOnlyList<EncyclopediaEmblemListCellViewModel> EmblemList);
}
