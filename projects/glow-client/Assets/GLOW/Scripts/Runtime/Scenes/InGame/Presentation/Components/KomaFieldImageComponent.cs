using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Common;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class KomaFieldImageComponent : UIObject
    {
        const float TrackingZoomRate = 1.25f;
        const float TrackingTargetLerpTime = 0.2f;
        const float EndTrackingLerpTime = 0.2f;

        [SerializeField] RawImage _fieldRawImage;

        Rect _initialUvRect;
        FieldViewPositionTracker _tracker;
        float _trackingTargetLerpTime;
        float _endTrackingLerpTime;

        public Rect InitialUvRect => _initialUvRect;
        public Rect CurrentUvRect => _fieldRawImage.uvRect;

        public void Setup(RenderTexture fieldRenderTexture, Rect komaUVRect)
        {
            _initialUvRect = komaUVRect;

            _fieldRawImage.texture = fieldRenderTexture;
            _fieldRawImage.uvRect = komaUVRect;

            SetupHeight(fieldRenderTexture, komaUVRect);
        }

        public void UpdateKomaFieldImage(out bool isUvUpdated)
        {
            isUvUpdated = false;

            if (_tracker != null)
            {
                var fieldUvRect = CalculateTrackingFieldUvRect(_tracker, TrackingZoomRate);

                if (_trackingTargetLerpTime > 0)
                {
                    var targetSize = fieldUvRect.size;
                    var targetCenter = fieldUvRect.center;
                    var lerpT = Time.deltaTime/ _trackingTargetLerpTime;

                    fieldUvRect.size = Vector2.Lerp(
                        _fieldRawImage.uvRect.size,
                        targetSize,
                        lerpT);

                    fieldUvRect.center = Vector2.Lerp(
                        _fieldRawImage.uvRect.center,
                        targetCenter,
                        lerpT);

                    _trackingTargetLerpTime -= Time.deltaTime;
                }

                _fieldRawImage.uvRect = fieldUvRect;
                isUvUpdated = true;
            }

            if (_endTrackingLerpTime > 0)
            {
                var uvRect = new Rect();
                var lerpT = Time.deltaTime/ _endTrackingLerpTime;

                uvRect.size = Vector2.Lerp(
                    _fieldRawImage.uvRect.size,
                    _initialUvRect.size,
                    lerpT);

                uvRect.center = Vector2.Lerp(
                    _fieldRawImage.uvRect.center,
                    _initialUvRect.center,
                    lerpT);

                _endTrackingLerpTime -= Time.deltaTime;

                _fieldRawImage.uvRect = uvRect;
                isUvUpdated = true;
            }
        }

        public void StartTracking(FieldViewPositionTracker tracker)
        {
            _tracker = tracker;
            _trackingTargetLerpTime = TrackingTargetLerpTime;
        }

        public void EndTracking()
        {
            if (_tracker == null) return;

            _tracker = null;
            _endTrackingLerpTime = EndTrackingLerpTime;
        }

        Rect CalculateTrackingFieldUvRect(FieldViewPositionTracker tracker, float zoomRate)
        {
            var uvRect = _initialUvRect;

            uvRect.width /= zoomRate;
            uvRect.height /= zoomRate;

            var targetUv = tracker.GetFieldUv();
            uvRect.center = targetUv;

            return AdjustUvRect(uvRect);
        }

        /// <summary>
        /// UV矩形が0〜1の範囲を越えないように調整
        /// </summary>
        Rect AdjustUvRect(Rect uvRect)
        {
            var adjustedUvRect = uvRect;

            if (uvRect.xMin < 0)
            {
                adjustedUvRect.xMin = 0;
                adjustedUvRect.xMax = uvRect.width;
            }
            else if (uvRect.xMax > 1)
            {
                adjustedUvRect.xMin = 1 - uvRect.width;
                adjustedUvRect.xMax = 1;
            }

            if (uvRect.yMin < 0)
            {
                adjustedUvRect.yMin = 0;
                adjustedUvRect.yMax = uvRect.height;
            }
            else if (uvRect.yMax > 1)
            {
                adjustedUvRect.yMin = 1 - uvRect.height;
                adjustedUvRect.yMax = 1;
            }

            return adjustedUvRect;
        }

        /// <summary>
        /// UVに合わせてComponentの高さを調整
        /// </summary>
        void SetupHeight(RenderTexture fieldRenderTexture, Rect komaUVRect)
        {
            var fieldTexHeightRate = (float)fieldRenderTexture.height / fieldRenderTexture.width;
            var uvRectHeightRate = komaUVRect.height / komaUVRect.width;
            var uiHeightRate = fieldTexHeightRate * uvRectHeightRate;

            var uiRect = RectTransform.rect;
            var sizeDelta = RectTransform.sizeDelta;
            RectTransform.sizeDelta = new Vector2(sizeDelta.x, uiRect.width * uiHeightRate);
        }
    }
}
