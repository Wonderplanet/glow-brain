using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.Home.Presentation.Components
{
    public class HomeMainQuestSymbolImage : UIComponent
    {
        [SerializeField] Image _image;

        public string AssetPath
        {
            set => UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_image, value);
        }
    }
}
