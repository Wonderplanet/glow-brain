using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterCell;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components
{
    /// <summary> フィルター全リセット </summary>
    public class AllFilterReset : UIComponent
    {
        [SerializeField] UITextButton _allResetButton;
        [SerializeField] ToggleAllSelectCancelButtonCell[] _toggleAllSelectCancelButtonCells;
        [SerializeField] FilterItem.FilterItem[] _filterItems;

        protected override void Awake()
        {
            base.Awake();
            _allResetButton.onClick.AddListener(OnAllReset);
        }

        void OnAllReset()
        {
            foreach (var allSelectCancelButtonCell in _toggleAllSelectCancelButtonCells)
            {
                allSelectCancelButtonCell.OnAllCancelButton();
            }

            foreach (var filterItem in _filterItems)
            {
                filterItem.IsToggleOn = false;
            }
        }
    }
}
