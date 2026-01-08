using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.Components
{
    /// <summary>
    /// 画像UIのコンポーネント
    /// UIに画像を置くときは基本これを使う
    /// </summary>
    [RequireComponent(typeof(Image))]
    public class UIImage : UIObject
    {
        [SerializeField] bool _isAutoClear;  // Awake時に画像をクリアするか（UIレイアウト時の仮画像を消すため）

        Image _image;
        bool _initialized;

        public Image Image
        {
            get
            {
                if (!_initialized) Initialize();
                return _image;
            }
        }

        public Sprite Sprite
        {
            get
            {
                if (!_initialized) Initialize();
                return _image.sprite;
            }
            set
            {
                if (!_initialized) Initialize();
                _image.sprite = value;
            }
        }

        public float Alpha
        {
            get
            {
                if (!_initialized) Initialize();
                return _image.color.a;
            }
            set
            {
                if (!_initialized) Initialize();
                var color = _image.color;
                color.a = value;
                _image.color = color;
            }
        }

        public Color Color
        {
            get
            {
                if (!_initialized) Initialize();
                return _image.color;
            }
            set
            {
                if (!_initialized) Initialize();
                _image.color = value;
            }
        }

        protected override void Awake()
        {
            base.Awake();
            Initialize();
        }

        void Initialize()
        {
            if (_initialized) return;
            _initialized = true;

            _image = GetComponent<Image>();

            if (_isAutoClear)
            {
                _image.sprite = null;
            }
        }
    }
}
