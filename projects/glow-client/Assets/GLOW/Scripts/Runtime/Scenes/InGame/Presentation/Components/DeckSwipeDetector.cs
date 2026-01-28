using System;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class DeckSwipeDetector : MonoBehaviour, IPointerDownHandler, IPointerUpHandler, IPointerMoveHandler
    {
        const float ThresholdDistance = 30; // タップ開始座標からの閾値

        Vector2 _touchPos;  // タッチ開始位置を保存する
        bool _needsSwipeCheck;

        RectTransform RectTransform => transform as RectTransform;

        public bool IsSwiping => _needsSwipeCheck;
        public Action OnSwipedLeft { get; set; }
        public Action OnSwipedRight { get; set; }

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
            _needsSwipeCheck = true;
        }

        public void OnPointerUp(PointerEventData eventData)
        {
            CheckSwipeAndInvokeAction(eventData);
            _needsSwipeCheck = false;
        }

        public void OnPointerMove(PointerEventData eventData)
        {
            CheckSwipeAndInvokeAction(eventData);
        }

        void CheckSwipeAndInvokeAction(PointerEventData eventData)
        {
            if (!_needsSwipeCheck) return;

            RectTransformUtility.ScreenPointToLocalPointInRectangle(
                RectTransform,
                eventData.position,
                Camera.main,
                out Vector2 touchPos);

            if (ThresholdDistance >= Mathf.Abs(_touchPos.x - touchPos.x))
            {
                return;
            }

            _needsSwipeCheck = false;

            if (_touchPos.x < touchPos.x)
            {
                OnSwipedRight?.Invoke();
            }
            else
            {
                OnSwipedLeft?.Invoke();
            }
        }
    }
}
