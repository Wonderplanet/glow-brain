using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.MessageBox.Presentation.View
{
    public class MessageBoxView : UIView
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] ChildScaler _childScaler;
        [SerializeField] UIObject _indicatorObject;
        [SerializeField] UIObject _noMessageObject;
        [SerializeField] Button _bulkOpenButton;
        [SerializeField] Button _bulkReceiveButton;

        public UICollectionView CollectionView => _collectionView;
        public UIObject Indicator => _indicatorObject;
        public UIObject NoMessageObject => _noMessageObject;

        public void SetBulkButton(bool isBulkReceive, bool isBulkOpen)
        {
            _bulkOpenButton.interactable = isBulkOpen;
            _bulkReceiveButton.interactable = isBulkReceive;
        }
        
        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play();
        }
    }
}