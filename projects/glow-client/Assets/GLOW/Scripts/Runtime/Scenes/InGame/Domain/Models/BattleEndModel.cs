using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.InGame.Domain.Models.BattleEndConditions;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record BattleEndModel(IReadOnlyList<IBattleEndConditionModel> Conditions)
    {
        public static BattleEndModel Empty { get; } = new(new List<IBattleEndConditionModel>());

        public bool TryGetCondition<T>(out T condition) where T : IBattleEndConditionModel
        {
            condition = Conditions.OfType<T>().FirstOrDefault();
            return condition is not null;
        }
    }
}
