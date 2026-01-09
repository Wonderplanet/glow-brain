using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.Home.Presentation.Components
{
    public class HomeKomaScrollBackground : UIObject
    {
        [SerializeField] UIImage _backgroundImage;

        /// <summary>
        /// _backgroundImageは傾いているので、このGameObjectの矩形の角に隙間ができないように_backgroundImageのスケールを調整する
        /// </summary>
        public void FitBackgroundImageSize()
        {
            var rect = RectTransform.rect;
            var diagonal = new Vector2(rect.width, rect.height);    // 対角線

            var angle = _backgroundImage.transform.localEulerAngles.z;
            var diagonalOnBgImage = Quaternion.Euler(0, 0, angle) * diagonal;
            var targetBgImageHeight = diagonalOnBgImage.y;

            var bgImageSize = _backgroundImage.RectTransform.sizeDelta;
            var scale = bgImageSize.y != 0f ? targetBgImageHeight / bgImageSize.y : 1f;

            _backgroundImage.RectTransform.localScale = new Vector3(scale, scale, 1f);

            Debug.Log("scale: " + scale +  " / bgImageSize" + bgImageSize.y + " / targetBgImageHeight" + targetBgImageHeight);
        }
    }
}
