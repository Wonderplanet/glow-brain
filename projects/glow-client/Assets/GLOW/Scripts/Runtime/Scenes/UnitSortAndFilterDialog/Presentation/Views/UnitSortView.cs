using System;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.ValueObjects;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Views
{
    public class UnitSortView : UIComponent
    {
        [SerializeField] UnitSortItem[] _sortItems;
        [SerializeField] UIToggleableComponentGroup _toggleableComponentGroup;
        [SerializeField] UnitSortItem _eventBonusSortItem;

        public void Initialize(UnitListSortType currentSortType, Action<UnitListSortType> onToggleChange)
        {
            foreach (var item in _sortItems)
            {
                item.SetUp(onToggleChange);
            }

            SetToggle(currentSortType);
        }
        
        public void SetEventBonusSortItemHidden(FilterBonusFlag bonusFlag)
        {
            _eventBonusSortItem.Hidden = !(bonusFlag == FilterBonusFlag.True);
        }

        public void SetToggle(UnitListSortType setSortType)
        {
            _toggleableComponentGroup.SetToggleOn(setSortType.ToString());
        }
    }
}
