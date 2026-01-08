using GLOW.Core.Presentation.Views.Interaction;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.AdventBattleMission.Presentation.View
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-7_ミッションアイコン（専用画面表示も実装に含む）
    /// </summary>
    public class AdventBattleMissionView : UIView
    {
        [SerializeField] ScreenActivityIndicatorView _indicator;
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] Button _bulkReceiveButton;

        public ScreenActivityIndicatorView Indicator => _indicator;
        public UICollectionView CollectionView => _collectionView;
        public Button BulkReceiveButton => _bulkReceiveButton;
    }
}