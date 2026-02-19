using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Pass;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using GLOW.Scenes.PassShop.Domain.Model;
using Zenject;

namespace GLOW.Scenes.PassShop.Domain.Factory
{
    public class HeldAdSkipPassInfoModelFactory : IHeldAdSkipPassInfoModelFactory
    {
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IHeldPassEffectRepository HeldPassEffectRepository { get; }
        
        HeldAdSkipPassInfoModel IHeldAdSkipPassInfoModelFactory.CreateHeldAdSkipPassInfo()
        {
            var nowTime = TimeProvider.Now;
            var adSkipPassEffectListModel = HeldPassEffectRepository.GetHeldPassEffectListModel()
                .SearchHeldPassEffectModelByEffectTypes(
                    new HashSet<ShopPassEffectType>()
                    {
                        ShopPassEffectType.AdSkip
                    },
                    nowTime)
                .MinBy(pass => pass.EndAt.Value) ?? HeldPassEffectModel.Empty;
            
            if (adSkipPassEffectListModel.IsEmpty()) return HeldAdSkipPassInfoModel.Empty;
            
            var adSkipPassName = MstShopProductDataRepository.GetShopPass(adSkipPassEffectListModel.MstShopPassId)
                .PassProductName;
            
            var holdRemainingTime = adSkipPassEffectListModel.EndAt - nowTime;

            return new HeldAdSkipPassInfoModel(
                adSkipPassName,
                holdRemainingTime);
        }
    }
}