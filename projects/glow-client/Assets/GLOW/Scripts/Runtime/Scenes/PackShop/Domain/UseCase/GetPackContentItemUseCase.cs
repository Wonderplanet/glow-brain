using System.Linq;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.PackShop.Domain.UseCase
{
    public class GetPackContentItemUseCase
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        public PlayerResourceModel GetPackContentItem(MasterDataId oprPackId, MasterDataId itemId)
        {
            var mstPack = MstShopProductDataRepository.GetPacks().First(mst => mst.ProductSubId == oprPackId);
            var packContents = MstShopProductDataRepository.GetPackContents(mstPack.Id);
            var mstPackContent =  packContents.FirstOrDefault(mst => mst.ResourceId == itemId);

            if(mstPackContent is null)
                return PlayerResourceModel.Empty;

            return PlayerResourceModelFactory.Create(
                mstPackContent.ResourceType,
                mstPackContent.ResourceId,
                new PlayerResourceAmount(mstPackContent.ResourceAmount.Value));
        }
    }
}
