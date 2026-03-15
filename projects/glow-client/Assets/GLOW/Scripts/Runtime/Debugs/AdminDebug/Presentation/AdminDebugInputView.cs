using UIKit;
using UnityEngine;

namespace GLOW.Debugs.AdminDebug.Presentation
{
    public sealed class AdminDebugInputView : UIView
    {
        [SerializeField] UICollectionView _collectionView;

        public UICollectionView CollectionView => _collectionView;
    }
}
