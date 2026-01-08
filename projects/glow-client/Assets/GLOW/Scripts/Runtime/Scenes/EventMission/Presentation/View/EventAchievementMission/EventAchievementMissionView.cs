using UIKit;
using UnityEngine;

namespace GLOW.Scenes.EventMission.Presentation.View.EventAchievementMission
{
    /// <summary>
    /// 61_ミッション
    /// 　61-1-2_アチーブメント（累計ミッション）
    /// </summary>
    public class EventAchievementMissionView : UIView
    {
        [SerializeField] UICollectionView _collectionView;
        public UICollectionView CollectionView => _collectionView;
    }
}