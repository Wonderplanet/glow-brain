using System;
using System.Collections.Generic;
using System.Security.Cryptography;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.HomeHelpDialog.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.Serialization;

namespace GLOW.Scenes.HomeHelpDialog.Presentation.Views.Components
{
    public class HomeHelpFoldingListComponent : UIObject, IFoldingListItemDelegate
    {
        [SerializeField] HomeHelpMainContentCell _mainContentCellPrefab;
        [SerializeField] Transform _mainContentCellContainer;

        List<HomeHelpMainContentCell> _cells = new List<HomeHelpMainContentCell>();

        const int PaddingTop = 10;
        const int PaddingBottom = 10;
        const int Space = 10;

        Action<bool> _setInteractable;

        public void SetUp(IReadOnlyList<HomeHelpMainContentCellViewModel> viewModels, Action<bool> setInteractable)
        {
            _setInteractable = setInteractable;
            foreach (var cell in _cells)
            {
                Destroy(cell.gameObject);
            }

            _cells.Clear();

            for (var i = 0; i < viewModels.Count; ++i)
            {
                var cell = Instantiate(_mainContentCellPrefab, _mainContentCellContainer);
                cell.SetUp(i, viewModels[i], this);
                _cells.Add(cell);
            }

            UpdateLayout();
        }

        public void UpdateLayout()
        {
            var rectTransform = _mainContentCellContainer.GetComponent<RectTransform>();
            var position = new Vector2(0, 0);
            position.y -= PaddingTop;

            foreach (var cell in _cells)
            {
                cell.RectTransform.anchoredPosition = position;
                position.y -= cell.RectTransform.sizeDelta.y + Space;
            }

            position.y -= PaddingBottom;

            var height = Mathf.Abs(position.y);
            rectTransform.sizeDelta = new Vector2(rectTransform.sizeDelta.x, height);
        }

        void IFoldingListItemDelegate.OnSelect(int index)
        {
            for(var i = 0 ; i < _cells.Count; i++)
            {
                var cell = _cells[i];
                if (i == index || cell.IsFold) continue;
                cell.SetFold(true);
            }
        }

        void IFoldingListItemDelegate.OnBeginUpdateLayout()
        {
            _setInteractable?.Invoke(false);
        }

        void IFoldingListItemDelegate.OnUpdateLayout()
        {
            UpdateLayout();
        }

        void IFoldingListItemDelegate.OnEndUpdateLayout()
        {
            _setInteractable?.Invoke(true);
        }
    }
}
