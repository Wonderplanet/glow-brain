using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitList.Domain.ValueObjects;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterCell
{
    public class FormationFilterCell : UIComponent
    {
        [SerializeField] FilterItem.FilterItem _achievedToggleableComponent;
        [SerializeField] FilterItem.FilterItem _notAchievedToggleableComponent;

        public void Initialize(FilterFormationModel model)
        {
            Hidden = !model.EnableFormationFlag;
            _achievedToggleableComponent.IsToggleOn = model.IsFilterAchievedSpecialRuleFlag;
            _notAchievedToggleableComponent.IsToggleOn = model.IsFilterNotAchieveSpecialRuleFlag;
        }

        public FilterAchievedSpecialRuleFlag GetAchievedToggleOn()
        {
            return new FilterAchievedSpecialRuleFlag(_achievedToggleableComponent.IsToggleOn);
        }

        public FilterNotAchieveSpecialRuleFlag GetNotAchieveToggleOn()
        {
            return new FilterNotAchieveSpecialRuleFlag(_notAchievedToggleableComponent.IsToggleOn);
        }
    }
}
