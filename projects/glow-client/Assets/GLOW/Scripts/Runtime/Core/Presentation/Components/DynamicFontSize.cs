using System;
using Cysharp.Text;
using TMPro;
using UniRx;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.Components
{
    /// <summary>
    /// テキストの自動サイズ調整
    /// AutoSizeでは調整出来ない、文字数でのサイズ調整を行う
    /// </summary>
    [RequireComponent(typeof(TextMeshProUGUI))]
    public class DynamicFontSize : MonoBehaviour
    {
        [Serializable]
        class AutoSizeOption
        {
            public float MinFontSize;
            public float MaxFontSize;
            public int MinCharacters;
            public int MaxCharacters;
        }

        [SerializeField] AutoSizeOption _autoSizeOption;

        TextMeshProUGUI _textMeshPro;

        void Awake()
        {
            _textMeshPro = GetComponent<TextMeshProUGUI>();

            _textMeshPro.ObserveEveryValueChanged(textMeshPro => textMeshPro.text)
                .Subscribe(AdjustFontSize).AddTo(this);
        }

        void AdjustFontSize(string text)
        {
            int characterCount = text.Length;

            var minCharacters = _autoSizeOption.MinCharacters;
            var maxCharacters = _autoSizeOption.MaxCharacters;
            var minFontSize = _autoSizeOption.MinFontSize;
            var maxFontSize = _autoSizeOption.MaxFontSize;

            float fontSize;

            // 最小文字数以下であれば最大フォントサイズ
            if (characterCount < minCharacters)
            {
                fontSize = maxFontSize;
            }
            // 最大文字数以上であれば最小フォントサイズ
            else if (characterCount > maxCharacters)
            {
                fontSize = minFontSize;
            }
            // 指定文字数以内であれば文字数に応じて可変
            else
            {
                // フォントサイズを計算（線形補間を使用）
                fontSize = Mathf.Lerp(maxFontSize, minFontSize, (float)(characterCount - minCharacters) / (maxCharacters - minCharacters));
            }

            _textMeshPro.fontSize = fontSize;
        }
    }
}
