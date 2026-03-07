using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkPanelMission.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ArtworkPanelMission.Presentation.Component
{
    public class ArtworkPanelMissionListComponent : UIObject
    {
        [SerializeField] UICollectionView _collectionView;
        
        public void Initialize(
            IUICollectionViewDataSource collectionViewDataSource,
            IUICollectionViewDelegate collectionViewDelegate)
        {
            _collectionView.DataSource = collectionViewDataSource;
            _collectionView.Delegate = collectionViewDelegate;
        }
        
        public void SetUpMissionList(IReadOnlyList<ArtworkPanelMissionCellViewModel> cellViewModels)
        {
            _collectionView.ReloadData();
        }
        
        public void ResetScrollPosition()
        {
            _collectionView.ScrollRect.verticalNormalizedPosition = 1.0f;
        }

        public ArtworkPanelMissionListCell DequeueReusableCell()
        {
            return _collectionView.DequeueReusableCell<ArtworkPanelMissionListCell>();
        }
    }
}