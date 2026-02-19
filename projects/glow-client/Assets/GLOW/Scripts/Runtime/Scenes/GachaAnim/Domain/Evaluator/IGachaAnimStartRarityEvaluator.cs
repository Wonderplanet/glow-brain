using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.GachaAnim.Domain.Evaluator
{
    public interface IGachaAnimStartRarityEvaluator
    {
        Rarity GetStartRarity(IReadOnlyList<Rarity> rarities, Rarity maxRarity);
    }
}
