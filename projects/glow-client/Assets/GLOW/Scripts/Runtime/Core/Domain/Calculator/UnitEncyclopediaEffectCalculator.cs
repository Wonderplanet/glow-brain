using System.Collections.Generic;
using System.Linq;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;

namespace GLOW.Core.Domain.Calculator
{
    public static class UnitEncyclopediaEffectCalculator
    {
        public static UnitGrade CalculateUnitEncyclopediaGrade(IReadOnlyList<UserUnitModel> userUnits)
        {
            // 初期グレードが1になった場合はそれぞれ-1して計算する
            var total = userUnits
                .Select(unit => unit.Grade.Value)
                .DefaultIfEmpty(UnitGrade.Empty.Value)
                .Sum(grade => grade);

            return new UnitGrade(total);
        }

        public static UnitEncyclopediaEffectValue CalculateUnitEncyclopediaUnitEffectValue(
            IReadOnlyList<MstUnitEncyclopediaRewardModel> mstRewards,
            IReadOnlyList<MstUnitEncyclopediaEffectModel> mstEffects,
            UnitGrade thresholdGrade,
            UnitEncyclopediaEffectType type)
        {
            var total = mstRewards
                // 報酬対象が閾値以下の報酬
                .Where(reward => reward.UnitEncyclopediaRank.Value <= thresholdGrade.Value)
                // 報酬と紐づく強化効果
                .Select(reward => mstEffects.Find(effect => effect.MstUnitEncyclopediaRewardId == reward.Id))
                // 対象とする強化効果の合計値
                .Where(effect => effect.EffectType == type)
                .Select(effect => (decimal)effect.Value.Value)
                .DefaultIfEmpty(0)
                .Sum();

            return new UnitEncyclopediaEffectValue(total);
        }
    }
}
