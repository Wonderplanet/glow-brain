using DG.Tweening;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.Shop.Presentation.View
{
    public class ShopCollectionView : UIView
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] ChildScaler _childScaler;

        public UICollectionView CollectionView => _collectionView;

        public void MoveToDiamondSection()
        {
            _collectionView.ScrollRect.DOVerticalNormalizedPos(1.0f, 0.5f).SetEase(Ease.InOutExpo);
        }

        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play();
        }

        public void CollectionScrollToTop()
        {
            _collectionView.ScrollRect.verticalNormalizedPosition = 1.0f;
        }
    }
}
