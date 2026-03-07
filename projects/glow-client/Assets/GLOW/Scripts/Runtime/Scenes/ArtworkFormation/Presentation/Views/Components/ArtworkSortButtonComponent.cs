using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using UnityEngine;

namespace GLOW.Scenes.ArtworkFormation.Presentation.Views.Components
{
    public class ArtworkSortButtonComponent : UIObject
    {
        [SerializeField] UIObject _sortAscendingIcon;
        [SerializeField] UIObject _sortDescendingIcon;

        public void SetSortAllow(ArtworkListSortOrder sortOrder)
        {
            _sortAscendingIcon.gameObject.SetActive(sortOrder == ArtworkListSortOrder.Ascending);
            _sortDescendingIcon.gameObject.SetActive(sortOrder == ArtworkListSortOrder.Descending);
        }
    }
}
