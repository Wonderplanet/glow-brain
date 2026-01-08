using GLOW.Core.Presentation.Modules.Audio;
using UIKit;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    /// <summary>
    /// パーティリストからのD＆D時にパーティリスト側のスクロールが干渉しないようにするためのScrollRect
    /// 長押し時にIsLongPressModeをtrueにして、Drag系イベントをDelegateに流す
    /// </summary>
    public class PartyFormationPageView : UIPageView
        ,IBeginDragHandler, IEndDragHandler, IDragHandler
    {
        public bool IsLongPressMode => _isLongPressMode;
        public new bool IsDragging => _isDragging || base.IsDragging;

        public IPartyFormationLongPressOverrideDelegate LongPressOverrideDelegate { get; set; }
        bool _isLongPressMode;
        bool _isDragging;

        public void SetLongPressMode(bool isLongPressMode)
        {
            _isLongPressMode = isLongPressMode;
        }

        void IBeginDragHandler.OnBeginDrag(PointerEventData eventData)
        {
            _isDragging = true;
            if (_isLongPressMode)
            {
                LongPressOverrideDelegate?.OnBeginDrag(eventData);
                return;
            }
            base.OnBeginDrag(eventData);
        }
        void IEndDragHandler.OnEndDrag(PointerEventData eventData)
        {
            _isDragging = false;
            if (_isLongPressMode)
            {
                LongPressOverrideDelegate?.OnEndDrag(eventData);
                return;
            }

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            base.OnEndDrag(eventData);
        }
        void IDragHandler.OnDrag(PointerEventData eventData)
        {
            if (_isLongPressMode)
            {
                LongPressOverrideDelegate?.OnDrag(eventData);
                return;
            }
            base.OnDrag(eventData);
        }
    }
}
