using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaTop.Presentation.ViewModels
{
    public record EncyclopediaTopViewModel(
        IReadOnlyList<EncyclopediaTopSeriesCellViewModel> Cells,
        UnitGrade TotalGrade,
        NotificationBadge BonusBadge);
}
