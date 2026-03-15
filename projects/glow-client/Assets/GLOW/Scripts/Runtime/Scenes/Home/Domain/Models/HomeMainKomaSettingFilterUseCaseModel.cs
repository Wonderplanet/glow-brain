using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.Models;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeMainKomaSettingFilterUseCaseModel(
        IReadOnlyList<MasterDataId> FilteredMstSeriesIds,
        IReadOnlyList<SeriesFilterTitleModel> SeriesTitles);
}