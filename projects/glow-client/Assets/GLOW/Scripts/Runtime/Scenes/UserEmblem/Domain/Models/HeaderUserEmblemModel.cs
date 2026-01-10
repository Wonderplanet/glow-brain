using System.Collections.Generic;

namespace GLOW.Scenes.UserEmblem.Domain.Models
{
    public record HeaderUserEmblemModel(
        HeaderUserEmblemCellModel CurrentEmblem,
        bool IsSeriesTabBadge,
        bool IsEventTabBadge,
        IReadOnlyList<HeaderUserEmblemCellModel> SeriesEmblemList,
        IReadOnlyList<HeaderUserEmblemCellModel> EventEmblemList);
}
