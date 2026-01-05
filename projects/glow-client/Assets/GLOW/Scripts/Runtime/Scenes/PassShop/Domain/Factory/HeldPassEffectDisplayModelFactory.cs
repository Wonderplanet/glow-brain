using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Scenes.PassShop.Domain.Model;
using Zenject;

namespace GLOW.Scenes.PassShop.Domain.Factory
{
    public class HeldPassEffectDisplayModelFactory : IHeldPassEffectDisplayModelFactory
    {
        [Inject] IHeldPassEffectRepository HeldPassEffectRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        
        IReadOnlyList<HeldPassEffectDisplayModel> IHeldPassEffectDisplayModelFactory.GetHeldPassEffectDisplayModels(
            HashSet<ShopPassEffectType> effectTypes)
        {
            var heldPassEffectModel = HeldPassEffectRepository.GetHeldPassEffectListModel();
            var heldPassEffectModelsRelateIdleIncentive = heldPassEffectModel.SearchHeldPassEffectModelByEffectTypes(
                    effectTypes, 
                    TimeProvider.Now)
                .ToList();

            var mstShopPasses = MstShopProductDataRepository.GetShopPasses();
            var displayModels = heldPassEffectModelsRelateIdleIncentive
                .Join(mstShopPasses, heldPass 
                        => heldPass.MstShopPassId, mstPass 
                        => mstPass.MstShopPassId, (heldPass, mstPass) 
                        => (heldPass, mstPass))
                .Select(pair => new HeldPassEffectDisplayModel(
                    pair.heldPass.MstShopPassId,
                    DisplayHoldingPassBannerAssetPath.FromAssetKey(pair.mstPass.PassAssetKey), 
                    pair.heldPass.EndAt - TimeProvider.Now))
                .ToList();

            return displayModels;
        }
    }
}