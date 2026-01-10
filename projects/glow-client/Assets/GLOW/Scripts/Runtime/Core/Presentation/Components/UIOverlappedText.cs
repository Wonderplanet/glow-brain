using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    /// <summary>
    /// 2重に配置した2つのテキストUIに同じ文言をセットするコンポーネント
    /// </summary>
    public class UIOverlappedText : UIObject
    {
        [SerializeField] UIText _text1;
        [SerializeField] UIText _text2;

        public void SetText(string text)
        {
            _text1.SetText(text);
            _text2.SetText(text);
        }
    }
}
