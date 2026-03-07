using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Scenes.PackShopProductInfo.Domain.ValueObjects;

namespace GLOW.Scenes.PackShopProductInfo.Domain.Models
{
    public record PackShopProductInfoContentModel(
        PlayerResourceModel ResourceModel,
        ProductName Name,
        PlayerResourceAmount Amount,
        IsTicketItemFlag IsTicketItemFlag);
}
