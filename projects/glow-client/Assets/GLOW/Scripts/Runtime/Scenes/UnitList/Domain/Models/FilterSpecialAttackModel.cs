using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.UnitList.Domain.Constants;

namespace GLOW.Scenes.UnitList.Domain.Models
{
    public record FilterSpecialAttackModel(IReadOnlyList<FilterSpecialAttack> SpecialAttacks)
    {
        public static FilterSpecialAttackModel Default { get; } = new FilterSpecialAttackModel(new List<FilterSpecialAttack>());

        public bool IsAnyFilter => SpecialAttacks.Count > 0;

        public bool IsOn(FilterSpecialAttack filterSpecialAttack)
        {
            return SpecialAttacks.Contains(filterSpecialAttack);
        }
    }
}
