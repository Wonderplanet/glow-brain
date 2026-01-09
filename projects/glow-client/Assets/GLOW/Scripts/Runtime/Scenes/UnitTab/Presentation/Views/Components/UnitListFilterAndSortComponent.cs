using System;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitList.Domain.Constants;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.UnitTab.Presentation.Views.Components
{
    public interface IUnitListFilterAndSortDelegate
    {
        void OnSortAndFilter();
        void OnSortAscending();
        void OnSortDescending();
    }

    [Serializable]
    public class NameToUiTextButton
    {
        public string _name;
        public UITextButton _button;
    }

    public class UnitListFilterAndSortComponent : MonoBehaviour
    {
        [Header("フィルター")]
        [SerializeField] Button _sortAndFilterButton;
        [SerializeField] UIToggleableComponent _filterToggleable;

        [Header("ソート")]
        [SerializeField] GameObject _sortAscendingIcon;
        [SerializeField] GameObject _sortDescendingIcon;
        [SerializeField] Button _sortButton;

        public IUnitListFilterAndSortDelegate Delegate { get; set; }

        void Awake()
        {
            _sortAndFilterButton.onClick.AddListener(SortAndFilter);
            _sortButton.onClick.AddListener(SwitchSort);
        }

        public void Setup(UnitListSortOrder sortOrder, bool isOnFilter)
        {
            _sortAscendingIcon.SetActive(UnitListSortOrder.Ascending == sortOrder);
            _sortDescendingIcon.SetActive(UnitListSortOrder.Descending == sortOrder);

            UpdateSortAndFilterButton(isOnFilter);
        }

        public void UpdateSortAndFilterButton(bool isAnyFilter)
        {
            _filterToggleable.IsToggleOn = isAnyFilter;
        }

        void SortAndFilter()
        {
            Delegate?.OnSortAndFilter();
        }

        void SwitchSort()
        {
            if (_sortAscendingIcon.activeSelf)
            {
                Delegate?.OnSortDescending();
            }
            else
            {
                Delegate?.OnSortAscending();
            }
        }
    }
}
