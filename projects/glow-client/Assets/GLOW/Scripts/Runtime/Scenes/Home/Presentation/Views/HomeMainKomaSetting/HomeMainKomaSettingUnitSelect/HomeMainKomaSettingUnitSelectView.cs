using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.HomeMainKomaSettingUnitSelect.Presentation
{
    public class HomeMainKomaSettingUnitSelectView : UIView
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] ChildScaler _childScaler;

        public void InitializeView(
            IUICollectionViewDataSource dataSource,
            IUICollectionViewDelegate viewDelegate)
        {
            _collectionView.DataSource = dataSource;
            _collectionView.Delegate = viewDelegate;
        }

        public void ReloadData()
        {
            _collectionView.ReloadData();
        }

        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play();
        }

    }
}
