using GLOW.Core.Domain.Constants;
using UnityEngine;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GashaAnimProduction(GashaAnimProductionType Value)
    {
        const float PromotionProbability = 0.05f;
        const float RarityProbability = 0.5f;

        public GashaAnimProductionType Value { get; } = Value;

        public static GashaAnimProduction Empty { get; } = new GashaAnimProduction(GashaAnimProductionType.None);

        public static GashaAnimProduction EvaluatePromotion(ResourceType ResourceType, Rarity Rarity)
        {
            var productionType = GashaAnimProductionType.None;

            if (ResourceType == ResourceType.Unit)
            {
                switch (Rarity)
                {
                    case Rarity.R:
                        productionType = GashaAnimProductionType.R;
                        break;
                    case Rarity.SR:
                        productionType = GashaAnimProductionType.SR;
                        break;
                    case Rarity.SSR:
                        productionType = GashaAnimProductionType.SSR;
                        break;
                    case Rarity.UR:
                        // 昇格あり(5%)
                        if (IsActionHappening(PromotionProbability))
                        {
                            // SRから昇格かSSRから昇格か(50%)
                            productionType = IsActionHappening(RarityProbability)
                                ? GashaAnimProductionType.SRtoUR
                                : GashaAnimProductionType.SSRtoUR;
                        }
                        // 昇格なし
                        else
                        {
                            productionType = GashaAnimProductionType.UR;
                        }
                        break;
                }
            }
            else
            {
                productionType = GashaAnimProductionType.Item;
            }

            return new GashaAnimProduction(productionType);

            // 確率判定
            bool IsActionHappening(float probability)
            {
                return Random.value < probability;
            }
        }
    }
}
