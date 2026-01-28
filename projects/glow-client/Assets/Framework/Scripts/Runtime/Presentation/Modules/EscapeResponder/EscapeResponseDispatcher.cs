using System.Collections.Generic;
using UnityEngine;
using WPFramework.Modules.Log;

namespace WPFramework.Presentation.Modules
{
    public sealed class EscapeResponseDispatcher : MonoBehaviour, IEscapeResponderRegistry
    {
        readonly List<IEscapeResponder> _responders = new List<IEscapeResponder>();

        void IEscapeResponderRegistry.Register(IEscapeResponder responder)
        {
            _responders.Add(responder);
        }

        void IEscapeResponderRegistry.Unregister(IEscapeResponder responder)
        {
            _responders.Remove(responder);
        }

        void Dispatch()
        {
            for (var i = _responders.Count - 1; i >= 0; i--)
            {
                if (!_responders[i].OnEscape())
                {
                    continue;
                }

                ApplicationLog.Log(nameof(EscapeResponseDispatcher), $"Dispatched {_responders[i].GetType().Name}");
                break;
            }
        }

        void Update()
        {
            if (Input.GetKeyUp(KeyCode.Escape))
            {
                Dispatch();
            }
        }

        void OnDestroy()
        {
            _responders.Clear();
        }
    }
}
