using UnityEngine;
using WPFramework.Presentation.Modules;

namespace WPFramework.Presentation.Extensions
{
    public static class IEscapeResponderExtension
    {
        public static void Bind(this IEscapeResponderRegistry registry, IEscapeResponder responder, Component component)
        {
            var binder = component.gameObject.GetComponent<EscapeResponderBinder>();
            if (!binder)
            {
                binder = component.gameObject.AddComponent<EscapeResponderBinder>();
            }

            binder.Bind(registry, responder);
        }
    }
}
