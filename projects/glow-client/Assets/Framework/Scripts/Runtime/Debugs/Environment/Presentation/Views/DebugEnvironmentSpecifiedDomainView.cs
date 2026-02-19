using UIKit;
using UnityEngine;

namespace WPFramework.Debugs.Environment.Presentation.Views
{
    public sealed class DebugEnvironmentSpecifiedDomainView : UIView
    {
        [SerializeField] UICollectionView _collectionView;
        public UICollectionView CollectionView => _collectionView;
    }
}
