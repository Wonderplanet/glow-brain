using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameUIBackground : UIObject
    {
        [SerializeField] UIImage _image;
        [SerializeField] Color _grayOutColor;

        public void GrayOut()
        {
            _image.Color = _grayOutColor;
        }

        public void ResetGrayOut()
        {
            _image.Color = Color.white;
        }
    }
}
