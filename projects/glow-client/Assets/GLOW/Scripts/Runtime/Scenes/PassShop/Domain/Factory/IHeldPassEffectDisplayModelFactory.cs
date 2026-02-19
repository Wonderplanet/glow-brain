using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.PassShop.Domain.Model;

namespace GLOW.Scenes.PassShop.Domain.Factory
{
    public interface IHeldPassEffectDisplayModelFactory
    {
        IReadOnlyList<HeldPassEffectDisplayModel> GetHeldPassEffectDisplayModels(
            HashSet<ShopPassEffectType> effectTypes);
    }
}