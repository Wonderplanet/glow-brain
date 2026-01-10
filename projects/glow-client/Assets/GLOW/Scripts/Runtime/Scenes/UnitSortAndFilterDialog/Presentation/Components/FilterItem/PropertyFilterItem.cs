using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterItem
{
    public class PropertyFilterItem : FilterItem
    {
        [SerializeField] UIText _titleText;

        public UnitAbilityType FilterType { get; private set; }

        public void Initialize(UnitAbilityType filterType, string filterText)
        {
            FilterType = filterType;
            _titleText.SetText(filterText);
        }
    }
}
