using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Scenes.PassShopProductDetail.Domain.Factory
{
    public interface IProductNameFactory
    {
        ProductName Create(ResourceType resourceType, MasterDataId resourceId);
    }
}