using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-2_敗北リザルト
    /// 　　53-2-1-2_コンティニュー
    /// 　　　53-2-1-2-3_プリズム購入画面（コンティニュー）
    /// </summary>
    public class DiamondPurchaseView : UIView
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] ChildScaler _childScaler;
        
        public UICollectionView CollectionView => _collectionView;
        
        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play();
        }
    }
}
