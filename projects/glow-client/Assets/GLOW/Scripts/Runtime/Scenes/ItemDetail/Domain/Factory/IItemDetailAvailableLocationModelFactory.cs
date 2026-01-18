using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ItemDetail.Domain.Models;

namespace GLOW.Scenes.ItemDetail.Domain.Factory
{
    public interface IItemDetailAvailableLocationModelFactory
    {
        ItemDetailAvailableLocationModel Create(ResourceType resourceType, MasterDataId masterDataId);
    }
}