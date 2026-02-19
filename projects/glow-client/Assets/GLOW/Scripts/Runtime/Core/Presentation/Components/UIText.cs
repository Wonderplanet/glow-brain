using Cysharp.Text;
using TMPro;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.Components
{
    /// <summary>
    /// テキストUIのコンポーネント。
    /// テキストのUIは基本これを使う。
    /// </summary>
    [RequireComponent(typeof(TextMeshProUGUI))]
    public class UIText : UIObject
    {
        [SerializeField] bool _isAutoClear = true;  // Awake時にテキストをクリアするか（UIレイアウト時の仮テキストを消すため）

        TextMeshProUGUI _textMeshPro;
        bool _isNotSetText = true;
        bool _initialized = false;

        protected TextMeshProUGUI TextMeshPro  => _textMeshPro;
        public string Text { get; private set; } = string.Empty;

        protected override void Awake()
        {
            base.Awake();
            if(!_initialized)
            {
                Initialize();
            }

            if (_isAutoClear || !_isNotSetText)
            {
                UpdateText();
            }
        }

        void Initialize()
        {
            _textMeshPro = GetComponent<TextMeshProUGUI>();
            _initialized = true;
        }

        public void SetText(string text)
        {
            Text = text;
            DidSetText();
        }

        public void SetMaterial(Material material)
        {
            if (!_initialized)
            {
                Initialize();
            }

            _textMeshPro.fontMaterial = material;
        }

        public void SetColor(Color color)
        {
            if (!_initialized)
            {
                Initialize();
            }

            _textMeshPro.color = color;
        }

        public void SetAlignment(TextAlignmentOptions alignment)
        {
            if (!_initialized)
            {
                Initialize();
            }

            _textMeshPro.alignment = alignment;
        }

        public void SetText<T1>(string format, T1 arg1)
        {
            Text = ZString.Format(format, arg1);
            DidSetText();
        }

        public void SetText<T1, T2>(string format, T1 arg1, T2 arg2)
        {
            Text = ZString.Format(format, arg1, arg2);
            DidSetText();
        }

        public void SetText<T1, T2, T3>(string format, T1 arg1, T2 arg2, T3 arg3)
        {
            Text = ZString.Format(format, arg1, arg2, arg3);
            DidSetText();
        }

        public void SetText<T1, T2, T3, T4>(string format, T1 arg1, T2 arg2, T3 arg3, T4 arg4)
        {
            Text = ZString.Format(format, arg1, arg2, arg3, arg4);
            DidSetText();
        }

        public void SetText<T1, T2, T3, T4, T5>(string format, T1 arg1, T2 arg2, T3 arg3, T4 arg4, T5 arg5)
        {
            Text = ZString.Format(format, arg1, arg2, arg3, arg4, arg5);
            DidSetText();
        }

        public void SetText<T1, T2, T3, T4, T5, T6>(string format, T1 arg1, T2 arg2, T3 arg3, T4 arg4, T5 arg5, T6 arg6)
        {
            Text = ZString.Format(format, arg1, arg2, arg3, arg4, arg5, arg6);
            DidSetText();
        }

        public void SetText<T1, T2, T3, T4, T5, T6, T7>(string format, T1 arg1, T2 arg2, T3 arg3, T4 arg4, T5 arg5, T6 arg6, T7 arg7)
        {
            Text = ZString.Format(format, arg1, arg2, arg3, arg4, arg5, arg6, arg7);
            DidSetText();
        }

        public void SetText<T1, T2, T3, T4, T5, T6, T7, T8>(string format, T1 arg1, T2 arg2, T3 arg3, T4 arg4, T5 arg5, T6 arg6, T7 arg7, T8 arg8)
        {
            Text = ZString.Format(format, arg1, arg2, arg3, arg4, arg5, arg6, arg7, arg8);
            DidSetText();
        }

        public void SetText<T1, T2, T3, T4, T5, T6, T7, T8, T9>(string format, T1 arg1, T2 arg2, T3 arg3, T4 arg4, T5 arg5, T6 arg6, T7 arg7, T8 arg8, T9 arg9)
        {
            Text = ZString.Format(format, arg1, arg2, arg3, arg4, arg5, arg6, arg7, arg8, arg9);
            DidSetText();
        }

        public void SetText<T1, T2, T3, T4, T5, T6, T7, T8, T9, T10>(string format, T1 arg1, T2 arg2, T3 arg3, T4 arg4, T5 arg5, T6 arg6, T7 arg7, T8 arg8, T9 arg9, T10 arg10)
        {
            Text = ZString.Format(format, arg1, arg2, arg3, arg4, arg5, arg6, arg7, arg8, arg9, arg10);
            DidSetText();
        }

        public void CrossFadeColor(Color targetColor, float duration, bool ignoreTimeScale, bool useAlpha)
        {
            if (!_initialized)
            {
                Initialize();
            }

            _textMeshPro.CrossFadeColor(targetColor, duration, ignoreTimeScale, useAlpha);
        }

        public bool IsReferenceEqualsTextMeshProUGUI(Graphic graphics)
        {
            if (!_initialized)
            {
                Initialize();
            }

            return _textMeshPro == graphics;
        }

        protected virtual void DidSetText()
        {
            _isNotSetText = false;

            if (_textMeshPro != null)
            {
                UpdateText();
            }
        }

        protected virtual void UpdateText()
        {
            if (!_initialized)
            {
                Initialize();
            }

            _textMeshPro.text = Text;
        }
    }
}
