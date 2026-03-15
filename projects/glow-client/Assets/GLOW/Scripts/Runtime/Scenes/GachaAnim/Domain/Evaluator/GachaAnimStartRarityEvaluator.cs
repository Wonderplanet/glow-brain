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

        public Rarity GetDisplayRarityForUR(Rarity rarity)
        {
            // URの場合にランダムで他のレアリティを選出
            if (rarity != Rarity.UR)
            {
                return rarity;
            }

            // 50%の確率でURをそのまま返す
            if (Randomizer.Range(0, 2) == 0)
            {
                return Rarity.UR;
            }

            // 残り50%でR、SR、SSRのいずれかを返す
            var lowerRarities = new[] { Rarity.R, Rarity.SR, Rarity.SSR };
            var randomIndex = Randomizer.Range(0, lowerRarities.Length);

            return lowerRarities[randomIndex];
        }
    }
}
