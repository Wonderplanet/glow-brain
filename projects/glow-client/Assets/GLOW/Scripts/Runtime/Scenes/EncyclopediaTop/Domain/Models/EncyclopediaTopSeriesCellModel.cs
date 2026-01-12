using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaTop.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaTop.Domain.Models
{
    public record EncyclopediaTopSeriesCellModel(
        MasterDataId MstSeriesId,
        SeriesIconImagePath ImagePath,
        SeriesName Name,
        EncyclopediaSeriesCount MaxCount,
        EncyclopediaSeriesCount UnlockCount,
        NotificationBadge Badge);
}
