using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.UnitList.Domain.Models
{
    public record FilterAbilityModel(IReadOnlyList<UnitAbilityType> AbilityTypes)
    {
        public static FilterAbilityModel Default { get; } = new FilterAbilityModel(new List<UnitAbilityType>());

        public bool IsAnyFilter => AbilityTypes.Count > 0;

        public bool IsOn(UnitAbilityType type)
        {
            return AbilityTypes.Contains(type);
        }
    }
}
