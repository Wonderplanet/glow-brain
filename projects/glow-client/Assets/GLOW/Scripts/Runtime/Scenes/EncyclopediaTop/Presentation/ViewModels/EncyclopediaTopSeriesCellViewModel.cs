using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaTop.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaTop.Presentation.ViewModels
{
    public record EncyclopediaTopSeriesCellViewModel(
        MasterDataId MstSeriesId,
        SeriesIconImagePath ImagePath,
        SeriesName Name,
        EncyclopediaSeriesCount MaxCount,
        EncyclopediaSeriesCount UnlockCount,
        NotificationBadge Badge);
}
