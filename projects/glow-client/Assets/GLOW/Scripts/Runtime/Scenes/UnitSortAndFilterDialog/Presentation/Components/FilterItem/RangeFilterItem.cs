using GLOW.Core.Domain.Constants;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterItem
{
    public class RangeFilterItem : FilterItem
    {
        [SerializeField] CharacterAttackRangeType _filterAttackRangeType;

        public CharacterAttackRangeType FilterType => _filterAttackRangeType;
    }
}
