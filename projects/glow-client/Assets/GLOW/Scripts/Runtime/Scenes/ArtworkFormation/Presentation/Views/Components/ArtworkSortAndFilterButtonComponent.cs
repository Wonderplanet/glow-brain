using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using UnityEngine;

namespace GLOW.Scenes.ArtworkFormation.Presentation.Views.Components
{
    public class ArtworkSortAndFilterButtonComponent : UIObject
    {
        [SerializeField] UIToggleableComponent _filterToggleable;

        [SerializeField] UIObject _sortAscendingIcon;
        [SerializeField] UIObject _sortDescendingIcon;

        public void SetSortAllow(ArtworkListSortOrder sortOrder, bool isOnFilter)
        {
            _sortAscendingIcon.gameObject.SetActive(sortOrder == ArtworkListSortOrder.Ascending);
            _sortDescendingIcon.gameObject.SetActive(sortOrder == ArtworkListSortOrder.Descending);

            UpdateSortAndFilterButton(isOnFilter);
        }

        public void UpdateSortAndFilterButton(bool isAnyFilter)
        {
            _filterToggleable.IsToggleOn = isAnyFilter;
        }
    }
}
