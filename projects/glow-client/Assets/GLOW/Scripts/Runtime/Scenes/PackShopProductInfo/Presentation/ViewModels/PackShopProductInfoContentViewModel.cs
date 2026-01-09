using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.PackShopProductInfo.Domain.ValueObjects;

namespace GLOW.Scenes.PackShopProductInfo.Presentation.ViewModels
{
    public record PackShopProductInfoContentViewModel(
        PlayerResourceIconViewModel ResourceIcon,
        ProductName Name,
        PlayerResourceAmount Amount,
        IsTicketItemFlag IsTicketItem);
}
