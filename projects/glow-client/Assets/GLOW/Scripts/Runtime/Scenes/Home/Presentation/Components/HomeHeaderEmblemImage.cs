using GLOW.Core.Presentation.Modules;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.Home.Presentation.Components
{
    public class HomeHeaderEmblemImage : UIComponent
    {
        [SerializeField] Image _image;
        [SerializeField] Sprite _defaultSprite;
        
        string _currentAssetPath;

        public void SetUp(string assetPath)
        {
            if (_currentAssetPath == assetPath) return;
            
            _currentAssetPath = assetPath;
            
            if (assetPath != "")
            {
                SpriteLoaderUtil.Clear(_image);
                UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_image, assetPath);
            }
            else
            {
                _image.sprite = _defaultSprite;
            }
        }
        
        public void SetDefaultSprite()
        {
            _currentAssetPath = null;
            _image.sprite = _defaultSprite;
        }
    }
}
