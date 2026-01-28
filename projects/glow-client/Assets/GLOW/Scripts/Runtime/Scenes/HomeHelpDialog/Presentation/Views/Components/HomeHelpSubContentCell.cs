using DG.Tweening;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.HomeHelpDialog.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.HomeHelpDialog.Presentation.Views.Components
{
    public class HomeHelpSubContentCell : UIObject
    {
        [SerializeField] UIText _title;
        [SerializeField] LayoutGroup _layoutGroup;
        [SerializeField] FoldingToggleButton _foldingToggleButton;
        [SerializeField] HomeHelpArticleComponent _articleComponent;

        // NOTE: リストオブジェクトをCanvas等でアルファ制御すると負荷になるのでオブジェクトを全部登録してアルファをかける
        [SerializeField] UIImage[] _images;
        [SerializeField] UIText[] _texts;

        IFoldingListItemDelegate _itemDelegate;
        Sequence _currentSequence;

        float _cellHeight;
        bool _isFold;
        bool _isInitializeArticle;
        int _index;

        public bool IsFold => _isFold;

        public bool InteractableButton
        {
            get { return _foldingToggleButton.Interactable; }
            set { _foldingToggleButton.Interactable = value; }
        }

        public void SetUp(
            int index,
            HomeHelpSubContentCellViewModel viewModel,
            IFoldingListItemDelegate itemDelegate)
        {
            _index = index;
            _itemDelegate = itemDelegate;
            _title.SetText(viewModel.Header);
            _articleComponent.SetUp(viewModel.Articles);
            _foldingToggleButton.IsToggleOn = true;
            _foldingToggleButton.OnToggleAction = OnToggleAction;
            _foldingToggleButton.Interactable = false;

            UpdateLayout();
            SetExpandingScale(0);
        }

        public void UpdateLayout()
        {
            _layoutGroup.CalculateLayoutInputVertical();

            LayoutRebuilder.ForceRebuildLayoutImmediate(this.RectTransform);
        }

        public void SetAlpha(float alpha)
        {
            foreach (var image in _images)
            {
                var color = image.Color;
                color.a = alpha;
                image.Color = color;
            }
            foreach (var text in _texts)
            {
                var color = Color.white;
                color.a = alpha;
                text.SetColor(color);
            }

            if (_isFold) return;
            _articleComponent.SetAlpha(alpha);
        }

        public void SetForceFold()
        {
            _foldingToggleButton.IsToggleOn = true;
            _isFold = true;
            SetExpandingScale(0);
        }

        void OnToggleAction(bool isToggleOn)
        {
            _itemDelegate?.OnSelect(_index);
            if (!isToggleOn && !_isInitializeArticle)
            {
                _isInitializeArticle = true;
                _articleComponent.Initialize();
            }
            SetFold(isToggleOn);
        }

        public void SetFold(bool isFold)
        {
            _foldingToggleButton.IsToggleOn = isFold;
            _isFold = isFold;
            if (isFold)
            {
                PlayFoldingAnimation();
            }
            else
            {
                PlayExpandingAnimation();
            }
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
                    value => _articleComponent.SetHeightRate(value),
                    end,
                    duration));
            _currentSequence.Join(
                DOTween.To(
                    () => start,
                    value => _articleComponent.SetAlpha(value),
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
                    value => _articleComponent.SetHeightRate(value),
                    end,
                    duration));
            _currentSequence.AppendCallback(() =>
            {
                _articleComponent.UpdateContentSize();
            });
            _currentSequence.Append(
                DOTween.To(
                    () => start,
                    value => _articleComponent.SetAlpha(value),
                    end,
                    duration));

            _currentSequence.OnComplete(() =>
            {
                _currentSequence = null;
                _itemDelegate?.OnEndUpdateLayout();
            });
        }

        void SetExpandingScale(float scale)
        {
            _articleComponent.SetHeightRate(scale);
            _articleComponent.SetAlpha(scale);
        }
    }
}
