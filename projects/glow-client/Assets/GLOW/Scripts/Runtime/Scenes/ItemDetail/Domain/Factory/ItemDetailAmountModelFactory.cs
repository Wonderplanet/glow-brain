using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ItemDetail.Domain.Models;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.ItemDetail.Domain.Factory
{
    public class ItemDetailAmountModelFactory : IItemDetailAmountModelFactory
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        
        public ItemDetailAmountModel Create(ResourceType resourceType, MasterDataId masterDataId)
        {
            PlayerCurrentAmount currentAmount = PlayerCurrentAmount.Empty;
            PlayerCurrentAmount paidAmount = PlayerCurrentAmount.Empty;
            switch (resourceType)
            {
                case ResourceType.Coin:
                case ResourceType.IdleCoin:
                {
                    var userParameterModel = GameRepository.GetGameFetch().UserParameterModel;
                    currentAmount = new PlayerCurrentAmount(userParameterModel.Coin.Value);
                    break;
                }
                case ResourceType.FreeDiamond:
                case ResourceType.PaidDiamond:
                {
                    var userParameterModel = GameRepository.GetGameFetch().UserParameterModel;
                    currentAmount = new PlayerCurrentAmount(userParameterModel.FreeDiamond.Value);
                    paidAmount = new PlayerCurrentAmount(userParameterModel.GetPaidDiamondFromPlatform(SystemInfoProvider.GetApplicationSystemInfo().PlatformId).Value);
                    break;
                }
                case ResourceType.Exp:
                {
                    var userParameterModel = GameRepository.GetGameFetch().UserParameterModel;
                    currentAmount = new PlayerCurrentAmount(userParameterModel.Exp.Value);
                    break;
                }
                case ResourceType.Item:
                {
                    var itemModel = GameRepository.GetGameFetchOther().UserItemModels
                        .FirstOrDefault(i => i.MstItemId == masterDataId) ?? UserItemModel.Empty;
                    currentAmount = new PlayerCurrentAmount(itemModel.Amount.Value);
                    break;
                }
            }

            return new ItemDetailAmountModel(currentAmount, paidAmount);
        }
    }
}