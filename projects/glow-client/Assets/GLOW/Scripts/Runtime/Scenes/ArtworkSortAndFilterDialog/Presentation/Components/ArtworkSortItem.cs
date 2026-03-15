using System;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Components
{
    public class ArtworkSortItem : UIComponent
    {
        [SerializeField] ArtworkListSortType _sortType;
        [SerializeField] Button _button;

        Action<ArtworkListSortType> _onToggleChange;

        public void SetUp(Action<ArtworkListSortType> onToggleChange)
        {
            _onToggleChange = onToggleChange;
            _button.onClick.AddListener(() => _onToggleChange?.Invoke(_sortType));
        }
    }
}
