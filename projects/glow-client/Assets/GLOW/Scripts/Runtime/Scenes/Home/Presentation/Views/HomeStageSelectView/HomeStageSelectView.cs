using UIKit;
using UnityEngine;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public sealed class HomeStageSelectView : UIView
    {
        [SerializeField] UICollectionView _stageCollectionView;

        public UICollectionView StageCollectionView => _stageCollectionView;
    }
}
