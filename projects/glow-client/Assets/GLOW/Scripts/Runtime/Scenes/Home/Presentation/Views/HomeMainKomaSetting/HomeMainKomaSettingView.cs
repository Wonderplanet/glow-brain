using GLOW.Core.Presentation.Components;
using GLOW.Scenes.Home.Domain.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.Serialization;

namespace GLOW.Scenes.Home.Presentation.Views.HomeMainKomaSetting
{
    public class HomeMainKomaSettingView : UIView
    {
        [SerializeField] UIPageView _pageView;
        [SerializeField] UIText _komaPatternName;

        public UIPageView PageView => _pageView;

        public void SetKomaPatternName(HomeMainKomaPatternName komaName)
        {
            _komaPatternName.SetText(komaName.Value);
        }

    }
}
