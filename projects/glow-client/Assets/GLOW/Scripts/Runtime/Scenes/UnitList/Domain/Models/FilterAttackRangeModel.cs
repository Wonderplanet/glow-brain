using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.UnitList.Domain.Models
{
    public record FilterAttackRangeModel(IReadOnlyList<CharacterAttackRangeType> AttackRangeTypes)
    {
        public static FilterAttackRangeModel Default { get; } = new FilterAttackRangeModel(new List<CharacterAttackRangeType>());

        public bool IsAnyFilter => AttackRangeTypes.Count > 0;

        public bool IsOn(CharacterAttackRangeType type)
        {
            return AttackRangeTypes.Contains(type);
        }
    }
}
