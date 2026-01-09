using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Core.Presentation.Components
{
    /// <summary>
    /// UIの基本クラス
    /// </summary>
    [RequireComponent(typeof(RectTransform))]
    public class UIObject : UIBehaviour, IUIObject
    {
        [SerializeField] bool _isAutoHide;  // Awake時に非表示にするか

        bool _isNotSetHidden = true;

        public RectTransform RectTransform => transform as RectTransform;
        
        public bool IsVisible
        {
            get => !Hidden;
            set => Hidden = !value;
        }

        public bool Hidden
        {
            get
            {
                return !gameObject.activeSelf;
            }
            set
            {
                _isAutoHide = false;
                gameObject.SetActive(!value);
            }
        }

        protected override void Awake()
        {
            base.Awake();

            if (_isAutoHide && _isNotSetHidden)
            {
                Hidden = true;
            }
        }
    }
}
