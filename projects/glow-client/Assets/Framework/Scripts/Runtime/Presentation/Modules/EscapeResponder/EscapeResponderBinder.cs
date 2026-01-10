using System;
using UnityEngine;

namespace WPFramework.Presentation.Modules
{
    public sealed class EscapeResponderBinder : MonoBehaviour
    {
        IEscapeResponderRegistry _registry;
        IEscapeResponder _responder;

        public void Bind(IEscapeResponderRegistry registry, IEscapeResponder responder)
        {
            _registry = registry ?? throw new ArgumentNullException(nameof(registry));
            _responder = responder ?? throw new ArgumentNullException(nameof(responder));

            registry.Register(responder);
        }

        void OnDestroy()
        {
            if (_registry == null || _responder == null)
            {
                return;
            }

            _registry.Unregister(_responder);
        }
    }
}
