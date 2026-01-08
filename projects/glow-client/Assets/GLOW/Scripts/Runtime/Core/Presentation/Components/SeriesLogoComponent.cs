using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Core.Presentation.Components
{
    public class SeriesLogoComponent : MonoBehaviour
    {
        [SerializeField] UIImage _seriesLogoImage;

        string _imagePath;
        public void Setup(SeriesLogoImagePath logoImagePath, Action onComplete = null)
        {
            if (_imagePath == logoImagePath.Value)
            {
                onComplete?.Invoke();
                return;
            }
            _imagePath = logoImagePath.Value;
            SpriteLoaderUtil.Clear(_seriesLogoImage.Image);
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _seriesLogoImage.Image,
                _imagePath,
                onComplete);
        }
    }
}
