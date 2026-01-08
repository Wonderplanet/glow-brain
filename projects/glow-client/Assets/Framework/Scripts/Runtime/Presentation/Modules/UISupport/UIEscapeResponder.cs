using UnityEngine;
using WPFramework.Presentation.Extensions;
using Zenject;

namespace WPFramework.Presentation.Modules
{
    public class UIEscapeResponder
    {
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public static UIEscapeResponder Main { get; private set; }

        public UIEscapeResponder()
        {
            Main = this;
        }

        public void Bind(IEscapeResponder responder, Component component)
        {
            EscapeResponderRegistry.Bind(responder, component);
        }
    }
}
