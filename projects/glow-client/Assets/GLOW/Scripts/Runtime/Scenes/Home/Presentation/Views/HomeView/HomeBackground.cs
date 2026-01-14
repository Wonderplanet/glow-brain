using GLOW.Core.Presentation.Components;
using GLOW.Scenes.Home.Presentation.Components;
using GLOW.Scenes.Home.Presentation.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public class HomeBackground : UIObject
    {
        [Header("ビルドイン背景")]
        [SerializeField] GameObject _buildinBackground;
        [SerializeField] HomeKomaScrollBackground _scrollKoma;
        [SerializeField] RectTransform _scrollGrayIcon;


        public void SetBasicBackground(BasicHomeBackgroundType type)
        {
            _buildinBackground.SetActive(true);

            _scrollKoma.gameObject.SetActive(type == BasicHomeBackgroundType.Default);
            _scrollGrayIcon.gameObject.SetActive(type == BasicHomeBackgroundType.ScrollIcon);

            _scrollKoma.FitBackgroundImageSize();
        }
    }
}
