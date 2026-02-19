using System;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.InGame.Presentation.Components.InGameUnitDetail
{
    public class InGameUnitDetailTouchLayerComponent : MonoBehaviour
    {
        public Action OnTouch { get; set; }

        bool _isUp;

        int _frameCount = 0;

        // InGameSpeedControlとPause画面の表示の順番の兼ね合いでUpdateではなく、FixedUpdateで処理する
        void FixedUpdate()
        {
            _frameCount++;
            if (_frameCount < 5) return;

#if UNITY_EDITOR
            if (!Input.GetMouseButton(0) && !_isUp)
            {
                OnTouch?.Invoke();
                _isUp = true;
            }
#else
            if (Input.touchCount == 0 && !_isUp)
            {
                OnTouch?.Invoke();
                _isUp = true;
            }
#endif
        }
    }
}
