using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ExchangeShop.Presentation.View
{
    public class FragmentTradeShopTopView : UIView
    {
        [SerializeField] UICollectionView _collectionView;

        public void Initialize(
            IUICollectionViewDataSource dataSource,
            IUICollectionViewDelegate collectionViewDelegate)
        {
            _collectionView.DataSource = dataSource;
            _collectionView.Delegate = collectionViewDelegate;
        }

        public void ReloadData()
        {
            _collectionView.ReloadData();
        }
    }
}
