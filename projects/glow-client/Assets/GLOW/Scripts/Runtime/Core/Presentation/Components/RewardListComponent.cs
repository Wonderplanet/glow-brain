using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class RewardListComponent : UIObject
    {
        [SerializeField] PlayerResourceIconList _iconList;

        [SerializeField] UIObject _scrollRoot;

        [SerializeField] RectTransform _scrollRootRectTransform;

        [SerializeField] RectTransform _scrollViewPortRectTransform;

        public Action<PlayerResourceIconViewModel> OnPlayerResourceIconTapped
        {
            get => _iconList.OnPlayerResourceIconTapped;
            set
            {
                _iconList.OnPlayerResourceIconTapped = value;
            }
        }

        // 1列分のサイズ
        const float ScrollColumnHeight = 148;
        // セルの幅
        const float CellWidth = 124;
        // セルの間隔
        const float CellSpace = 22;

        public async UniTask PlayRewardListAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> iconViewModels,
            int startScrollRow,
            CancellationToken cancellationToken)
        {
            _iconList.Hidden = false;
            var isCellAnimationCompleted = false;
            _iconList.SetupAndReload(iconViewModels, true, startScrollRow, onComplete: () => isCellAnimationCompleted = true);
            AdjustScrollContentArea(iconViewModels);

            await UniTask.WaitUntil(() => isCellAnimationCompleted, cancellationToken: cancellationToken);
        }

        public void ShowRewardList(IReadOnlyList<PlayerResourceIconViewModel> iconViewModels,  int startScrollRow)
        {
            _iconList.Hidden = false;

            // SetupAndReloadの中でCollectionCellAnimationを生成する
            _iconList.SetupAndReload(iconViewModels, true, startScrollRow,  onComplete: () => { });

            // CollectionCellAnimationのアニメーションをスキップする
            _iconList.PlayerResourceIconAnimation.SkipAnimation();

            AdjustScrollContentArea(iconViewModels);
        }

        void AdjustScrollContentArea(IReadOnlyList<PlayerResourceIconViewModel> iconViewModels)
        {
            if (iconViewModels.Count <= 0) return;

            if (iconViewModels.Count < 5)
            {
                _scrollRootRectTransform.sizeDelta = new Vector2(
                    (CellWidth * (iconViewModels.Count) + (CellSpace * (iconViewModels.Count + 1))),
                    _scrollRootRectTransform.sizeDelta.y
                );
            }
            else
            {
                _scrollRootRectTransform.sizeDelta = new Vector2(
                    (CellWidth * 5 + (CellSpace * 6)),
                    _scrollRootRectTransform.sizeDelta.y
                );
            }

            var rowCorrection = 0;
            if (iconViewModels.Count % 5 != 0) rowCorrection = 1;

            // 表示アイテム数に応じてスクロールの高さ調整
            // ReSharper disable once PossibleLossOfFraction
            var scrollSize = ScrollColumnHeight * (iconViewModels.Count / 5 + rowCorrection) + CellSpace;
            if (scrollSize > _scrollViewPortRectTransform.rect.height) scrollSize = _scrollViewPortRectTransform.rect.height;
            _scrollRoot.RectTransform.sizeDelta = new Vector2(_scrollRoot.RectTransform.sizeDelta.x, scrollSize);
        }
    }
}
