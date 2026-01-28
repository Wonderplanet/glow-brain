using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ExchangeShop.Presentation.View
{
    public class ExchangeContentTopView : UIView
    {
        [SerializeField] UICollectionView _collectionView;

        public void InitializeView(
            IUICollectionViewDelegate viewDelegate,
            IUICollectionViewDataSource dataSource)
        {
            _collectionView.Delegate = viewDelegate;
            _collectionView.DataSource = dataSource;
        }

        public void ReloadData()
        {
            _collectionView.ReloadData();
        }
    }
}
