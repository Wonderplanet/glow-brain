using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ItemDetail.Domain.Factory;
using GLOW.Scenes.ItemDetail.Domain.Models;
using Zenject;

namespace GLOW.Scenes.ItemDetail.Domain.UseCase
{
    public class ShowItemDetailUseCase
    {
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IItemDetailAmountModelFactory ItemDetailAmountModelFactory { get; }
        [Inject] IItemDetailAvailableLocationModelFactory ItemDetailAvailableLocationModelFactory { get; }

        public ItemDetailWithTransitModel GetItemDetail(ResourceType resourceType, MasterDataId masterDataId, PlayerResourceAmount amount)
        {
            PlayerResourceModel playerResource;

            playerResource = PlayerResourceModelFactory.Create(resourceType, masterDataId, amount);

            var amountModel = ItemDetailAmountModelFactory.Create(resourceType, masterDataId);
            var availableLocationModel = ItemDetailAvailableLocationModelFactory.Create(resourceType, masterDataId);

            return new ItemDetailWithTransitModel(
                playerResource,
                amountModel,
                availableLocationModel);
        }
    }
}
