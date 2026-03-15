using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.EventBonusUnitList.Presentation.Views
{
    /// <summary>
    /// 45_強化クエスト
    /// 　42-5_1日N回強化クエスト
    /// 　　45-1-7-1_ ボーナスキャラ簡易表示
    /// 　　45-1-7-2_ ボーナスキャラ一覧ダイアログ
    /// </summary>
    public class EventBonusUnitListView : UIView
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] int _viewportHeightLimit;
        [SerializeField] UIObject _statusBonusText;
        [SerializeField] UIObject _rewardBonusText;

        public UICollectionView CollectionView => _collectionView;

        public void SetBonusText(QuestType questType)
        {
            var isEnhance = questType == QuestType.Enhance;
            _statusBonusText.Hidden = isEnhance;
            _rewardBonusText.Hidden = !isEnhance;
        }

        public void ReformCollectionViewSize()
        {
            var scrollRect = _collectionView.ScrollRect;
            var contentSize = scrollRect.content.sizeDelta;
            var size = _collectionView.RectTransform.sizeDelta;
            size.y = _viewportHeightLimit < contentSize.y ? _viewportHeightLimit : contentSize.y;
            _collectionView.RectTransform.sizeDelta = size;
        }
    }
}
