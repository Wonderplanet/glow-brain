using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Extensions;
using WonderPlanet.UnityStandard.Extension;

namespace GLOW.Core.Domain.Models.Pass
{
    public record HeldPassEffectListModel(
        IReadOnlyList<HeldPassEffectModel> PassEffectModels)
    {
        public static HeldPassEffectListModel Empty { get; } = new(
            new List<HeldPassEffectModel>()
        );

        public PassEffectValue GetPassEffectValue(ShopPassEffectType type, DateTimeOffset now)
        {
            var totalEffectValue = PassEffectModels
                .Where(model => model.ShopPassEffectType == type)
                .Where(model => CalculateTimeCalculator.IsValidTime(
                    now,
                    model.StartAt.Value,
                    model.EndAt.Value))
                .Sum(model => model.PassEffectValue.Value);
            
            return totalEffectValue == 0 ? 
                PassEffectValue.Empty : 
                new PassEffectValue(totalEffectValue);
        }

        public IReadOnlyList<HeldPassEffectModel> SearchHeldPassEffectModelByEffectTypes(HashSet<ShopPassEffectType> typeSet, DateTimeOffset now)
        {
            // GroupByで重複分を抜く
            // 指定タイプがない場合は全部取得する
            var effectModels = PassEffectModels
                .Where(model => typeSet.IsEmpty() || typeSet.Contains(model.ShopPassEffectType))
                .Where(model => CalculateTimeCalculator.IsValidTime(
                    now,
                    model.StartAt.Value,
                    model.EndAt.Value))
                .GroupBy(model => model.MstShopPassId)
                .Select(group => group.FirstOrDefault(HeldPassEffectModel.Empty))
                .ToList();

            return effectModels;
        }

        public bool IsValidPassEffect(ShopPassEffectType type, DateTimeOffset now)
        {
            return !GetPassEffectValue(type, now).IsEmpty();
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}