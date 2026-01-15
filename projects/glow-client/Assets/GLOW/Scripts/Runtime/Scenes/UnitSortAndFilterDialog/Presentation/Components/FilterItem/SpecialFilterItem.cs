using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Presentation.Extension;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterItem
{
    public class SpecialFilterItem : FilterItem
    {
        [SerializeField] UIText _titleText;

        public FilterSpecialAttack FilterType { get; private set; }

        public void Initialize(FilterSpecialAttack filterType)
        {
            FilterType = filterType;
            _titleText.SetText(filterType.ToDisplayText());
        }
    }
}
