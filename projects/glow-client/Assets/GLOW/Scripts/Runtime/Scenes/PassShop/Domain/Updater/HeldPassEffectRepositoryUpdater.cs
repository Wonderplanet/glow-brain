using System.Linq;
using GLOW.Core.Domain.Models.Pass;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.PassShop.Domain.Updater
{
    public class HeldPassEffectRepositoryUpdater : IHeldPassEffectRepositoryUpdater
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IHeldPassEffectRepository HeldPassEffectRepository { get; }
        
        public void RegisterPassEffect()
        {
            var nowTime = TimeProvider.Now;
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var validPassModels = gameFetchOther.UserShopPassModels
                .Where(pass => nowTime < pass.EndAt.Value)
                .ToList();
            var validPassEffects = validPassModels
                .Select(pass => pass.MstShopPassId)
                .SelectMany(id => MstShopProductDataRepository.GetShopPassEffects(id))
                .ToList();
            
            var heldPassEffectModels = validPassEffects
                .Join(
                    validPassModels, 
                    effect => effect.MstShopPassId, 
                    user => user.MstShopPassId, 
                    (effect, user) => (effect, user))
                .Select(
                    userAndEffect => new HeldPassEffectModel(
                        userAndEffect.effect.MstShopPassId, 
                        userAndEffect.effect.ShopPassEffectType, 
                        userAndEffect.effect.EffectValue, 
                        userAndEffect.user.StartAt, 
                        userAndEffect.user.EndAt))
                .ToList();
            
            HeldPassEffectRepository.SetHeldPassEffectModels(new HeldPassEffectListModel(heldPassEffectModels));
        }
    }
}