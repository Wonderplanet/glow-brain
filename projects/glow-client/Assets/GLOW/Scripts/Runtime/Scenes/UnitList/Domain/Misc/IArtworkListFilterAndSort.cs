using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.UnitList.Domain.Misc
{
    public interface IArtworkListFilterAndSort
    {
        IReadOnlyList<MstArtworkModel> FilterAndSort(
            IReadOnlyList<UserArtworkModel> userArtworks,
            IReadOnlyList<MstArtworkModel> mstArtworks,
            IReadOnlyList<MstInGameArtworkEffectModel> mstArtworkEffectModels,
            ArtworkSortFilterCategoryModel sortFilterCategory,
            IReadOnlyList<MstSeriesModel> seriesModels);
    }
}
