using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Scenes.PackShopProductInfo.Domain.Models;
using GLOW.Scenes.PackShopProductInfo.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.PackShopProductInfo.Domain.UseCase
{
    public class GetPackProductInfoUseCase
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IValidatedStoreProductRepository ValidatedStoreProductRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public PackShopProductInfoModel GetProductInfo(MasterDataId oprProductId)
        {
            var mstPack = MstShopProductDataRepository.GetPacks().First(mst => mst.ProductSubId == oprProductId);
            var product = ValidatedStoreProductRepository.GetValidatedStoreProducts()
                .Where(product => product.MstStoreProduct.ProductType == ProductType.Pack)
                .Where(product => product.MstStoreProduct.StartDate <= TimeProvider.Now && TimeProvider.Now <= product.MstStoreProduct.EndDate)
                .FirstOrDefault(product => product.MstStoreProduct.OprProductId == mstPack.ProductSubId);

            var mstPackContents = MstShopProductDataRepository.GetPackContents(mstPack.Id);
            var contents = mstPackContents
                .Where(mst => !mst.IsBonus.Flg)
                .OrderByDescending(mst => mst.DisplayOrder)
                .Select(Translate)
                .ToList();

            if (null != product && 0 < product.MstStoreProduct.PaidAmount.Value)
            {
                var amount = new PlayerResourceAmount(product.MstStoreProduct.PaidAmount.Value);
                var playerResourceModel = PlayerResourceModelFactory.Create(
                    ResourceType.PaidDiamond,
                    MasterDataId.Empty,
                    amount);
                var name = ProductName.FromTypeAndName(
                    ResourceType.PaidDiamond,
                    ItemName.Empty,
                    CharacterName.Empty,
                    ProductResourceAmount.Empty);

                contents.Insert(0, new PackShopProductInfoContentModel(
                    playerResourceModel,
                    name,
                    amount,
                    IsTicketItemFlag.False));
            }

            var bonuses = mstPackContents
                .Where(mst => mst.IsBonus.Flg)
                .OrderByDescending(mst => mst.DisplayOrder)
                .Select(Translate)
                .ToList();

            return new PackShopProductInfoModel(contents, bonuses);
        }

        PackShopProductInfoContentModel Translate(MstPackContentModel model)
        {
            var mstItem = model.ResourceType == ResourceType.Item
                ? MstItemDataRepository.GetItem(model.ResourceId)
                : MstItemModel.Empty;
            var mstCharacterModel = model.ResourceType == ResourceType.Unit
                ? MstCharacterDataRepository.GetCharacter(model.ResourceId)
                : MstCharacterModel.Empty;

            var name = ProductName.FromTypeAndName(
                model.ResourceType,
                mstItem.Name,
                mstCharacterModel.Name,
                ProductResourceAmount.Empty);

            var amount = model.ResourceAmount.ToPlayerResourceAmount();
            var playerResourceModel = PlayerResourceModelFactory.Create(
                model.ResourceType,
                model.ResourceId,
                amount);

            var isTicketItemFlag = new IsTicketItemFlag(mstItem.Type == ItemType.GachaTicket);
            return new PackShopProductInfoContentModel(
                playerResourceModel,
                name,
                amount,
                isTicketItemFlag);
        }
    }
}
