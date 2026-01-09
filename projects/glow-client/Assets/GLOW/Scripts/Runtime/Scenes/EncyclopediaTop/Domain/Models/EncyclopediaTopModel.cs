using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaTop.Domain.Models
{
    public record EncyclopediaTopModel(
        IReadOnlyList<EncyclopediaTopSeriesCellModel> Cells,
        UnitGrade TotalGrade,
        NotificationBadge BonusBadge);
}
