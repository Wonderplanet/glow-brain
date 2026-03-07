using System.Collections.Generic;
using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.Models;

namespace GLOW.Scenes.HomeMainKomaSettingFilter.Presentation
{
    public record HomeMainKomaSettingFilterViewModel(
        FilterSeriesModel FilterSeriesModel,
        IReadOnlyList<SeriesFilterTitleModel> SeriesFilterTitleModels
        );
}
