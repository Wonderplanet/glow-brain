using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class KomaBackgroundComponent : UIObject
    {
        const int BackgroundOffsetY = 37;   // コマ枠の内側から40px下にずらす（コマ枠が3pxくらいある）

        [SerializeField] RawImage _backgroundRawImage;

        Rect _backgroundInitialUvRect;

        public void Setup(
            Sprite komaBackgroundSprite,
            KomaBackgroundOffset komaBackgroundOffset,
            RectTransform komaRectTransform,
            float komaSetWidth)
        {
            _backgroundRawImage.texture = komaBackgroundSprite.texture;

            _backgroundInitialUvRect = CalculateInitialUvRect(
                komaBackgroundSprite.texture,
                komaBackgroundOffset,
                komaRectTransform,
                komaSetWidth);

            _backgroundRawImage.uvRect = _backgroundInitialUvRect;
        }

        public void UpdatePosAndScale(Rect fieldUvRect, Rect fieldInitialUvRect)
        {
            _backgroundRawImage.uvRect = CalculateUvRect(fieldUvRect, fieldInitialUvRect);
        }

        Rect CalculateInitialUvRect(Texture texture, KomaBackgroundOffset komaBackgroundOffset, RectTransform komaRectTransform, float komaSetWidth)
        {
            var uvRect = new Rect(0, 0, 1, 1);
            if (texture == null || texture.width == 0 || texture.height == 0) return uvRect;

            // コマの矩形からUV矩形のサイズを求める
            var komaRect = komaRectTransform.rect;
            uvRect.width = komaRect.width / texture.width;
            uvRect.height = komaRect.height / texture.height;

            // コマ1行の背景がちょうど繋がるようにコマの位置でUVを調整
            var center = uvRect.center;
            center.x = 0.5f + komaRectTransform.anchoredPosition.x / texture.width;
            center.y += (float)BackgroundOffsetY / texture.height;

            // 指定された水平方向のオフセットを反映
            var sizeDiff = texture.width - komaSetWidth;
            var offset = sizeDiff * 0.5f * komaBackgroundOffset.Value;
            center.x -= offset / texture.width;

            uvRect.center = center;

            return uvRect;
        }

        Rect CalculateUvRect(Rect fieldUvRect, Rect fieldInitialUvRect)
        {
            if (fieldInitialUvRect.width == 0 || fieldInitialUvRect.height == 0) return _backgroundInitialUvRect;

            var fieldUvSizeRate = new Vector2(
                fieldUvRect.width / fieldInitialUvRect.width,
                fieldUvRect.height / fieldInitialUvRect.height);

            var fieldUvDiff = fieldUvRect.center - fieldInitialUvRect.center;
            var fieldUvDiffRate = new Vector2(
                fieldUvDiff.x / fieldInitialUvRect.width,
                fieldUvDiff.y / fieldInitialUvRect.height);

            var bgUvRectDiff = new Vector2(
                _backgroundInitialUvRect.width * fieldUvDiffRate.x,
                _backgroundInitialUvRect.height * fieldUvDiffRate.y);

            var uvRect = new Rect();

            uvRect.size = new Vector2(
                _backgroundInitialUvRect.width * fieldUvSizeRate.x,
                _backgroundInitialUvRect.height * fieldUvSizeRate.y);

            uvRect.center = _backgroundInitialUvRect.center + bgUvRectDiff;

            return uvRect;
        }
    }
}
