using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitTab.Presentation.Views.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitList.Presentation.Views
{
    public class UnitListView : UIView
    {
        [SerializeField] UICollectionView _unitListView;
        [SerializeField] ChildScaler _childScaler;
        [SerializeField] UnitListFilterAndSortComponent _filterAndSort;

        public UICollectionView UnitList => _unitListView;
        public UnitListFilterAndSortComponent FilterAndSort => _filterAndSort;
        
        public float ScrollVerticalNormalizedPosition
        {
            get => _unitListView.ScrollRect.verticalNormalizedPosition;
            set => _unitListView.ScrollRect.verticalNormalizedPosition = value;
        }
        
        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play();
        }
    }
}
