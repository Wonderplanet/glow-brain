using System;
using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.EncyclopediaReward.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.EncyclopediaReward.Presentation.Views
{
    public class EncyclopediaRewardListComponent : UIObject
    {
        [SerializeField] EncyclopediaRewardListCategoryComponent _releasedList;
        [SerializeField] EncyclopediaRewardListCategoryComponent _lockList;
        [SerializeField] ScrollRect _scrollRect;
        [SerializeField] RectTransform _lockListLineObj;

        public static int CellOffsetCount => 2;

        public void Setup(IReadOnlyList<EncyclopediaRewardListCellViewModel> releasedViewModels,
            IReadOnlyList<EncyclopediaRewardListCellViewModel> lockViewModels,
            Action<EncyclopediaRewardListCellViewModel> onSelectReward,
            Action<EncyclopediaRewardListCellViewModel> onSelectLockReward)
        {
            _releasedList.Setup(releasedViewModels, onSelectReward);
            _lockList.Setup(lockViewModels, onSelectLockReward);

            _lockListLineObj.SetAsLastSibling();
            LayoutRebuilder.ForceRebuildLayoutImmediate(_scrollRect.content);

            var viewportHeight = Math.Abs(_scrollRect.viewport.rect.height);
            var releasedListHeight = Math.Abs(_releasedList.Rect.height);
            var lockListHeight = Math.Abs(_lockList.Rect.height);

            // 未開放リストがリスト領域より小さい場合はリストの一番上にスクロールする
            if (lockListHeight < viewportHeight)
            {
                _scrollRect.verticalNormalizedPosition = 1f;
                return;
            }
            // 開放済みリストがリスト領域より小さい場合はスクロールしない
            if (viewportHeight >= releasedListHeight) return;

            var contentSizeY = Math.Abs(_scrollRect.content.rect.height) - viewportHeight;
            if( contentSizeY <= 0) return;
            var cellOffsetHeight = _releasedList.CellSize.y * CellOffsetCount;
            var cellNormalizedPosition = (releasedListHeight - cellOffsetHeight) / contentSizeY;
            _scrollRect.verticalNormalizedPosition = cellNormalizedPosition;
        }
    }
}
