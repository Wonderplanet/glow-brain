using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.GameModeSelect.Presentation
{
    public class GameModeSelectView : UIView
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] Button _closeButton;
        [SerializeField] Button _backgroundButton;
        [Header("アニメーション向け")]
        [SerializeField] GameObject _rootObject;
        [SerializeField] float _closeStartY = 590f;
        [SerializeField] float _openStartY = 570f;

        public UICollectionView CollectionView => _collectionView;
        public Button CloseButton => _closeButton;
        public Button BackgroundButton => _backgroundButton;
        public GameObject RootObject => _rootObject;
        public float CloseStartY => _closeStartY;
        public float OpenStartY => _openStartY;
    }
}
