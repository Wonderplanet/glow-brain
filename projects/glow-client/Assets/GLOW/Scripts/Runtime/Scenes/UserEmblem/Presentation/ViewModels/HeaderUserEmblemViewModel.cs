using System.Collections.Generic;

namespace GLOW.Scenes.UserEmblem.Presentation.ViewModels
{
    public record HeaderUserEmblemViewModel(
        HeaderUserEmblemCellViewModel CurrentEmblem,
        bool IsSeriesTabBadge,
        bool IsEventTabBadge,
        IReadOnlyList<HeaderUserEmblemCellViewModel> SeriesEmblemList,
        IReadOnlyList<HeaderUserEmblemCellViewModel> EventEmblemList);
}
