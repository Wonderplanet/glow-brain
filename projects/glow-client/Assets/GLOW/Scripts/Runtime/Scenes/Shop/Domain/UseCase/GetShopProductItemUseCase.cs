using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.UseCase
{
    public class GetShopProductItemUseCase
    {
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        public PlayerResourceModel GetPlayerResource(ResourceType type, MasterDataId id, ProductResourceAmount amount)
        {
            // TODO : 色々なところに同じような処理があるので統一したい
            return PlayerResourceModelFactory.Create(type, id, new PlayerResourceAmount(amount.Value));
        }
    }
}
