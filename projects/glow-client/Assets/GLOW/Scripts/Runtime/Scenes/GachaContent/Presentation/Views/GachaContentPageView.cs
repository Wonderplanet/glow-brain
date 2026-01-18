using UIKit;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.GachaContent.Presentation.Views
{
    public class GachaContentPageView : UIPageView
        ,IBeginDragHandler, IEndDragHandler, IDragHandler
    {
        // public bool IsLongPressMode => _isLongPressMode;
        // public new bool IsDragging => _isDragging || base.IsDragging;

        // public IPartyFormationLongPressOverrideDelegate LongPressOverrideDelegate { get; set; }
        // bool _isLongPressMode;
        // bool _isDragging;
        //
        // public void SetLongPressMode(bool isLongPressMode)
        // {
        //     _isLongPressMode = isLongPressMode;
        // }
        //
        // void IBeginDragHandler.OnBeginDrag(PointerEventData eventData)
        // {
        //     _isDragging = true;
        //     if (_isLongPressMode)
        //     {
        //         LongPressOverrideDelegate?.OnBeginDrag(eventData);
        //         return;
        //     }
        //     base.OnBeginDrag(eventData);
        // }
        // void IEndDragHandler.OnEndDrag(PointerEventData eventData)
        // {
        //     _isDragging = false;
        //     if (_isLongPressMode)
        //     {
        //         LongPressOverrideDelegate?.OnEndDrag(eventData);
        //         return;
        //     }
        //
        //     base.OnEndDrag(eventData);
        // }
        // void IDragHandler.OnDrag(PointerEventData eventData)
        // {
        //     if (_isLongPressMode)
        //     {
        //         LongPressOverrideDelegate?.OnDrag(eventData);
        //         return;
        //     }
        //     base.OnDrag(eventData);
        // }

    }
}
