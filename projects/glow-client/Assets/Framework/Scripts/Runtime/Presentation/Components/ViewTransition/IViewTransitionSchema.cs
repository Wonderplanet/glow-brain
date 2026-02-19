using UnityEngine;

namespace WPFramework.Presentation.Components
{
    public interface IViewTransitionSchema
    {
        Coroutine AppearanceTransition(bool isAppearing);
    }
}
