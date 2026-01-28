using UnityEngine.EventSystems;
using UnityEngine.UI;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public interface IPartyFormationLongPressOverrideDelegate
    {
        void OnBeginDrag(PointerEventData eventData);
        void OnDrag(PointerEventData eventData);
        void OnEndDrag(PointerEventData eventData);
    }

    /// <summary>
    /// キャラ一覧からのD＆D時にキャラ一覧側のスクロールが干渉しないようにするためのScrollRect
    /// 長押し時にIsLongPressModeをtrueにして、Drag系イベントをDelegateに流す
    /// </summary>
    public class PartyFormationScrollRect : ScrollRect
    {
        bool _isLongPressMode;
        bool _isDragging;

        public IPartyFormationLongPressOverrideDelegate LongPressOverrideDelegate { get; set; }
        public bool IsDragging => _isDragging;
        public bool IsLongPressMode => _isLongPressMode;

        public void SetLongPressMode(bool enable)
        {
            _isLongPressMode = enable;
        }

        public override void OnBeginDrag(PointerEventData eventData)
        {
            _isDragging = true;
            if (_isLongPressMode)
            {
                LongPressOverrideDelegate?.OnBeginDrag(eventData);
                return;
            }
            base.OnBeginDrag(eventData);
        }

        public override void OnEndDrag(PointerEventData eventData)
        {
            _isDragging = false;
            if (_isLongPressMode)
            {
                LongPressOverrideDelegate?.OnEndDrag(eventData);
                return;
            }
            base.OnEndDrag(eventData);
        }

        public override void OnDrag(PointerEventData eventData)
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
