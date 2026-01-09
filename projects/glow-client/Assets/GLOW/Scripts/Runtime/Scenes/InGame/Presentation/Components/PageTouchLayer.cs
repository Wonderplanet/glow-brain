using System;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class PageTouchLayer : UIObject, IPointerDownHandler, IPointerUpHandler, IPointerMoveHandler
    {
        // タッチされたときに呼ぶコールバック
        // このUI上のタッチ位置を渡す
        public Action<Vector2> OnTouch { get; set; }

        // タッチ開始位置を保存する
        Vector2 _touchPos;

        // 閾値以上に移動しているかどうか true→移動していない
        bool _isThreshold ;

        // タップ開始座標からの閾値
        const float ThresholdDistance = 5;

        public void OnPointerDown(PointerEventData eventData)
        {
            // タッチ位置を求める
            RectTransformUtility.ScreenPointToLocalPointInRectangle(
                RectTransform,
                eventData.position,
                Camera.main,
                out Vector2 touchPos);

            // タップ開始座標を一時保存
            _touchPos = touchPos;
            // 判定初期化
            _isThreshold = true;
        }

        public void OnPointerUp(PointerEventData eventData)
        {
            // タップ開始座標から閾値以上離れていなければ実行
            if (_isThreshold)
            {
                RectTransformUtility.ScreenPointToLocalPointInRectangle(
                    RectTransform,
                    eventData.position,
                    Camera.main,
                    out Vector2 touchPos);

                _isThreshold = false;
                // 実行
                OnTouch?.Invoke(touchPos);
            }
        }

        public void OnPointerMove(PointerEventData eventData)
        {
            // タップされている場合のみ実行
            if (_isThreshold)
            {
                RectTransformUtility.ScreenPointToLocalPointInRectangle(
                    RectTransform,
                    eventData.position,
                    Camera.main,
                    out Vector2 touchPos);

                // 閾値よりもタップ位置が離れたら実行しない
                if (ThresholdDistance < Vector2.Distance(touchPos, _touchPos))
                {
                    _isThreshold = false;
                }
            }
        }
    }
}
