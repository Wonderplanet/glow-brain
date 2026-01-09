using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterItem;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterCell
{
    public class ColorFilterCell : UIComponent
    {
        [SerializeField] List<ColorFilterItem> _filterItems;

#if UNITY_EDITOR
        protected override void Reset()
        {
            base.Reset();
            _filterItems = gameObject.GetComponentsInChildren<ColorFilterItem>().ToList();
        }
#endif

        public void Initialize(FilterColorModel model)
        {
            foreach (var item in _filterItems)
            {
                item.IsToggleOn = model.IsOn(item.FilterType);
            }
        }

        public IReadOnlyList<CharacterColor> GetOnToggleTypes()
        {
            var filterTypes = _filterItems
                .Where(item => item.IsToggleOn)
                .Select(item => item.FilterType)
                .ToList();
            return filterTypes;
        }
    }
}
