using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using Zenject;

namespace GLOW.Scenes.PassShopProductDetail.Domain.Factory
{
    public class ProductNameFactory : IProductNameFactory
    {
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstEmblemRepository MstEmblemRepository { get; }

        ProductName IProductNameFactory.Create(
            ResourceType resourceType,
            MasterDataId resourceId)
        {
            switch (resourceType)
            {
                case ResourceType.Coin:
                    return ProductName.Coin;
                case ResourceType.FreeDiamond:
                    return ProductName.FreeDiamond;
                case ResourceType.PaidDiamond:
                    return ProductName.PaidDiamond;
                case ResourceType.Exp:
                    return ProductName.Exp;
                case ResourceType.Item:
                    return CreateByItem(resourceId);
                case ResourceType.Character:
                case ResourceType.Unit:
                    return CreateByUnit(resourceId);
                case ResourceType.Emblem:
                    return CreateByEmblem(resourceId);
                default:
                    return ProductName.Empty;
            }
        }

        ProductName CreateByItem(MasterDataId itemId)
        {
            var item = MstItemDataRepository.GetItem(itemId);
            return item.Name.ToProductName();
        }

        ProductName CreateByUnit(MasterDataId unitId)
        {
            var character = MstCharacterDataRepository.GetCharacter(unitId);
            return character.Name.ToProductName();
        }

        ProductName CreateByEmblem(MasterDataId emblemId)
        {
            var emblem = MstEmblemRepository.GetMstEmblemFirstOrDefault(emblemId);
            return emblem.Name.ToProductName();
        }
    }
}
