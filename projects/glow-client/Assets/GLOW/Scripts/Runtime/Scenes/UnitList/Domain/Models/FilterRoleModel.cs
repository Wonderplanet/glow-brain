using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.UnitList.Domain.Models
{
    public record FilterRoleModel(IReadOnlyList<CharacterUnitRoleType> FilterRoles)
    {
        public static FilterRoleModel Default { get; } = new FilterRoleModel(new List<CharacterUnitRoleType>());

        public bool IsAnyFilter => FilterRoles.Count > 0;

        public bool IsOn(CharacterUnitRoleType type)
        {
            return FilterRoles.Contains(type);
        }
    }
}
