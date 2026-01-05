using GLOW.Core.Presentation.Modules;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.Home.Presentation.Components
{
    public class HomeHeaderAvatarImage : UIComponent
    {
        [SerializeField] Image _image;
        
        string _currentAssetPath;

        public void SetUp(string assetPath)
        {
            if (_currentAssetPath == assetPath) return;
            
            _currentAssetPath = assetPath;
            _image.gameObject.SetActive(true);
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_image, assetPath);
        }

        public void ClearImage()
        {
            _currentAssetPath = null;
            SpriteLoaderUtil.Clear(_image);
            _image.gameObject.SetActive(false);
        }
    }
}
