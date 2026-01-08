using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Debugs.AdminDebug.Presentation
{
    public sealed class AdminDebugView : UIView
    {

        [SerializeField] Text _applicationTimeText;
        [SerializeField] Text _applicationEnvText;
        [SerializeField] UICollectionView _commandCollectionView;

        public Text ApplicationTimeText => _applicationTimeText;
        public Text EnvNameText => _applicationEnvText;
        public UICollectionView CommandCollectionView => _commandCollectionView;
    }
}
