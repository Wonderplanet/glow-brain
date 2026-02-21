using GLOW.Core.Domain.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Core.Presentation.Components
{
    public class InGameSpecialRuleTitleCell : UIComponent
    {
        [SerializeField] Image _image;

        public void Setup(SeriesLogoImagePath logoLogoImagePath)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_image, logoLogoImagePath.Value);
        }
    }
}
