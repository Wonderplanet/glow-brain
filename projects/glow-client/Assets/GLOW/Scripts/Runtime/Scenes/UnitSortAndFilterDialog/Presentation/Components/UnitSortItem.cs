using System;
using GLOW.Scenes.UnitList.Domain.Constants;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components
{
    public class UnitSortItem : UIComponent
    {
        [SerializeField] UnitListSortType SortType;
        [SerializeField] Button _button;

        Action<UnitListSortType> _onToggleChange;

        public void SetUp(Action<UnitListSortType> onToggleChange)
        {
            _onToggleChange = onToggleChange;
            _button.onClick.AddListener(() => _onToggleChange?.Invoke(SortType));
        }
    }
}
