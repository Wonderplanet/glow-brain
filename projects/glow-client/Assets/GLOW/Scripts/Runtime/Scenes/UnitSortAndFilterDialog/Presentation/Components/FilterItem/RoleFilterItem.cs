using GLOW.Core.Domain.Constants;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterItem
{
    public class RoleFilterItem : FilterItem
    {
        [SerializeField] CharacterUnitRoleType _roleType;

        public CharacterUnitRoleType FilterType => _roleType;
    }
}
