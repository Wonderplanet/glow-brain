using GLOW.Core.Presentation.Components;
using GLOW.Scenes.QuestSelect.Presentation;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.QuestSelectList.Presentation
{
    public class QuestSelectListView : UIView
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] ChildScaler _childScaler;

        public void InitializeView(IUICollectionViewDelegate viewDelegate, IUICollectionViewDataSource dataSource)
        {
            _collectionView.Delegate = viewDelegate;
            _collectionView.DataSource = dataSource;
        }

        public QuestSelectListCell DequeueReusableCell()
        {
            return _collectionView.DequeueReusableCell<QuestSelectListCell>();
        }

        public void ReloadData()
        {
            _collectionView.ReloadData();
        }

        public void StartChildScalerAnimation()
        {
            _childScaler.Play();
        }
    }
}
