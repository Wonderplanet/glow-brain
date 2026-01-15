using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Notice;
using GLOW.Core.Extensions;
using GLOW.Scenes.Notice.Domain.Converter;
using GLOW.Scenes.Notice.Domain.Model;
using Zenject;

namespace GLOW.Scenes.Notice.Domain.Factory
{
    public class InGameNoticeModelFactory : IInGameNoticeModelFactory
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        
        NoticeModel IInGameNoticeModelFactory.Create(OprNoticeModel model)
        {
            var destinationScene = ConvertToDestinationScene(model);
            return new NoticeModel(
                model.Id,
                model.DisplayType,
                model.DisplayFrequencyType,
                model.Title,
                new NoticeMessage(HtmlToRichTextConverter.ConvertToRichText(model.Message.Value)),  // HTML形式なのでリッチテキストへ置き換え
                model.BannerUrl,
                model.DestinationType,
                destinationScene,
                model.DestinationPathDetail,
                model.TransitionButtonName);
        }
        
        DestinationScene ConvertToDestinationScene(OprNoticeModel model)
        {
            if (model.DestinationPath == NoticeDestinationPath.ShopFree)
            {
                return DestinationScene.Shop;
            }
            else if (model.DestinationPath == NoticeDestinationPath.ShopPaid)
            {
                return GetShopPaidDestinationScene(model.DestinationPath, model.DestinationPathDetail);
            }
            else if (model.DestinationPath == NoticeDestinationPath.Pass)
            {
                return DestinationScene.Pass;
            }
            
            return model.DestinationPath.ToDestinationScene();
        }

        DestinationScene GetShopPaidDestinationScene(
            NoticeDestinationPath destinationPath,
            NoticeDestinationPathDetail destinationTypeDetail)
        {
            var productId = destinationTypeDetail.ToMasterDataId();
            var targetStoreProduct = MstShopProductDataRepository.GetStoreProducts()
                .FirstOrDefault(product => product.OprProductId == productId, MstStoreProductModel.Empty);

            if (targetStoreProduct.ProductType == ProductType.Diamond)
            {
                return DestinationScene.Shop;
            }
            else if (targetStoreProduct.ProductType == ProductType.Pack)
            {
                return DestinationScene.Pack;
            }
            else if (targetStoreProduct.ProductType == ProductType.Pass)
            {
                return DestinationScene.Pass;
            }
            
            return destinationPath.ToDestinationScene();
        }
    }
}