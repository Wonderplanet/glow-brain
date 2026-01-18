using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using WonderPlanet.RandomGenerator;
using Zenject;

namespace GLOW.Scenes.GachaAnim.Domain.Evaluator
{
    public class GachaAnimStartRarityEvaluator : IGachaAnimStartRarityEvaluator
    {
        [Inject] IRandomizer Randomizer { get; }
        public Rarity GetStartRarity(IReadOnlyList<Rarity> rarities, Rarity maxRarity)
        {
            // 排出物のレアリティからランダムで初期レアリティ選出
            var index = Randomizer.Range(0, rarities.Count);
            var startRarity = rarities[index];

            return startRarity >= maxRarity ? maxRarity : startRarity;
        }
    }
}
