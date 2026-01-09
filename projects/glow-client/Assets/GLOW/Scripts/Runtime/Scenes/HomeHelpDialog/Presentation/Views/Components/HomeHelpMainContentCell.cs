using System.Collections.Generic;
using DG.Tweening;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.HomeHelpDialog.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.HomeHelpDialog.Presentation.Views.Components
{
    public class HomeHelpMainContentCell : UIObject, IFoldingListItemDelegate
    {
        [SerializeField] HomeHelpSubContentCell _subContentCellPrefab;
        [SerializeField] UIObject _titleArea;
        [SerializeField] UIText _titleOnOpen;
        [SerializeField] UIText _titleOnClose;
        [SerializeField] UIObject _subContentCellContainer;
        [SerializeField] FoldingToggleButton _foldingToggleButton;
        [SerializeField] LayoutElement _layoutElement;
        [SerializeField] LayoutElement _containerLayoutElement;
        [SerializeField] VerticalLayoutGroup _containerLayoutGroup;

        List<HomeHelpSubContentCell> _cells = new ();
        IFoldingListItemDelegate _itemDelegate;
        float _containerHeight;
        bool _isFold;
        int _index;
        int _updateActivateCount;
        Sequence _currentSequence;

        public bool IsFold => _isFold;

        public void SetUp(
            int index,
            HomeHelpMainContentCellViewModel viewModel,
            IFoldingListItemDelegate itemDelegate)
        {
            _index = index;
            _itemDelegate = itemDelegate;
            _titleOnOpen.SetText(viewModel.Header);
            _titleOnClose.SetText(viewModel.Header);
            _foldingToggleButton.IsToggleOn = true;
            _foldingToggleButton.OnToggleAction = OnToggleAction;

            for (var i = 0; i < viewModel.SubContents.Count; ++i)
            {
                var cellViewModel = viewModel.SubContents[i];
                var cell = Instantiate(_subContentCellPrefab, _subContentCellContainer.RectTransform);
                cell.SetUp(i, cellViewModel, this);
                _cells.Add(cell);
            }

            UpdateContainerSize();
            UpdateLayout();
            SetForceFold();
        }

        void UpdateContainerSize()
        {
            _containerLayoutGroup.enabled = true;
            _containerLayoutElement.enabled = false;

            _containerLayoutGroup.CalculateLayoutInputVertical();
            LayoutRebuilder.ForceRebuildLayoutImmediate(this.RectTransform);
            _containerHeight = _subContentCellContainer.RectTransform.sizeDelta.y;
            _containerLayoutElement.preferredHeight = _containerHeight;

            _containerLayoutElement.enabled = true;
            _containerLayoutGroup.enabled = false;
        }

        void UpdateLayout()
        {
            UpdateSubContentCellListLayout();

            var rectTransform = this.GetComponent<RectTransform>();
            var sizeDelta = rectTransform.sizeDelta;
            sizeDelta.y = _titleArea.RectTransform.sizeDelta.y;
            sizeDelta.y += _subContentCellContainer.RectTransform.sizeDelta.y;
            _layoutElement.preferredHeight = sizeDelta.y;

            _itemDelegate?.OnUpdateLayout();
        }

        void UpdateSubContentCellListLayout()
        {
            float height = _containerLayoutGroup.padding.top;

            foreach (var cell in _cells)
            {
                height += cell.RectTransform.sizeDelta.y + _containerLayoutGroup.spacing;
            }

            height += _containerLayoutGroup.padding.bottom;

            _containerLayoutElement.preferredHeight = height;
        }

        void SetForceFold()
        {
            _isFold = true;
            foreach (var subCell in _cells)
            {
                subCell.SetForceFold();
                subCell.InteractableButton = false;
            }
            SetExpandingScale(0);
        }

        void OnToggleAction(bool isToggleOn)
        {
            _itemDelegate?.OnSelect(_index);
            SetFold(isToggleOn);
        }

        public void SetFold(bool isFold)
        {
            _foldingToggleButton.IsToggleOn = isFold;
            _isFold = isFold;
            if (isFold)
            {
                PlayFoldingAnimation();
                foreach (var subCell in _cells)
                {
                    if (!subCell.IsFold)
                    {
                        subCell.SetForceFold();
                    }
                    subCell.InteractableButton = false;
                }
            }
            else
            {
                PlayExpandingAnimation();

                foreach (var subCell in _cells)
                {
                    subCell.InteractableButton = true;
                }
            }
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
            _containerLayoutGroup.enabled = true;
            _containerLayoutElement.enabled = false;
            ++_updateActivateCount;

            _itemDelegate?.OnBeginUpdateLayout();
        }

        void IFoldingListItemDelegate.OnUpdateLayout()
        {
            UpdateLayout();
        }

        void IFoldingListItemDelegate.OnEndUpdateLayout()
        {
            --_updateActivateCount;
            if(0 < _updateActivateCount) return;

            _containerLayoutElement.preferredHeight = _subContentCellContainer.RectTransform.sizeDelta.y;
            _containerLayoutElement.enabled = true;
            _containerLayoutGroup.enabled = false;

            _itemDelegate?.OnEndUpdateLayout();
        }

        void PlayFoldingAnimation()
        {
            var start = 1f;
            var end = 0f;
            var duration = 0.2f;

            // 実行中のシーケンスがあればキル（完了処理も実行）
            _currentSequence?.Kill(true);

            // 枠を閉じると同時にフェードアウト
            SetExpandingScale(start);
            _itemDelegate?.OnBeginUpdateLayout();
            _currentSequence = DOTween.Sequence();
            _currentSequence.Join(
                DOTween.To(
                    () => start,
                    value => _containerLayoutElement.preferredHeight = value * _containerHeight,
                    end,
                    duration));
            _currentSequence.Join(
                DOTween.To(
                    () => start,
                    SetSubContentCellAlpha,
                    end,
                    duration / 2));

            _currentSequence.OnComplete(() =>
            {
                _currentSequence = null;
                _itemDelegate?.OnEndUpdateLayout();
            });
        }

        void PlayExpandingAnimation()
        {
            var start = 0f;
            var end = 1f;
            var duration = 0.2f;

            // 実行中のシーケンスがあればキル（完了処理も実行）
            _currentSequence?.Kill(true);

            // 枠を広げた後にフェードイン
            SetExpandingScale(start);
            _itemDelegate?.OnBeginUpdateLayout();

            _currentSequence = DOTween.Sequence();
            _currentSequence.Append(
                DOTween.To(
                    () => start,
                    value => _containerLayoutElement.preferredHeight = value * _containerHeight,
                    end,
                    duration));
            _currentSequence.AppendCallback(() => UpdateContainerSize());
            _currentSequence.Append(
                DOTween.To(
                    () => start,
                    SetSubContentCellAlpha,
                    end,
                    duration / 2));

            _currentSequence.OnComplete(() =>
            {
                _currentSequence = null;
                _itemDelegate?.OnEndUpdateLayout();
            });
        }

        void SetExpandingScale(float scale)
        {
            _containerLayoutElement.preferredHeight = scale * _containerHeight;
            SetSubContentCellAlpha(scale);
        }

        void SetSubContentCellAlpha(float alpha)
        {
            foreach(var minerCell in _cells)
            {
                minerCell.SetAlpha(alpha);
            }
        }
    }
}
