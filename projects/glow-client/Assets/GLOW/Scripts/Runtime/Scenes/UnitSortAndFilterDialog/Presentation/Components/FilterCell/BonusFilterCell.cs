using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitList.Domain.ValueObjects;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterCell
{
    public class BonusFilterCell : UIComponent
    {
        [SerializeField] FilterItem.FilterItem _toggleableComponent;

        public void Initialize(FilterBonusModel model)
        {
            this.Hidden = !model.EnableBonus;
            _toggleableComponent.IsToggleOn = model.BonusFilterFlag;
        }

        public FilterBonusFlag GetToggleOn()
        {
            return new FilterBonusFlag(_toggleableComponent.IsToggleOn);
        }
    }
}
