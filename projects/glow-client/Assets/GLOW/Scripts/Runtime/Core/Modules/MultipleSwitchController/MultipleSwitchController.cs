using System;
using System.Collections.Generic;

namespace GLOW.Core.Modules.MultipleSwitchController
{
    public class MultipleSwitchController : IDisposable
    {
        public Action<bool> OnStateChanged { get; set; }

        readonly List<MultipleSwitchHandler> _handlers = new ();

        public static MultipleSwitchHandler CreateHandler()
        {
            return new MultipleSwitchHandler();
        }

        public MultipleSwitchHandler TurnOn()
        {
            return TurnOn(CreateHandler());
        }

        public MultipleSwitchHandler TurnOn(MultipleSwitchHandler handler)
        {
            _handlers.Add(handler);

            handler.AddDisposedCallback(OnHandlerDisposed);

            // 最初のTurnOnでOnStateChangedをtrueで呼ぶ。重複してTurnOnしたときはOnStateChangedを呼ばない
            if (_handlers.Count == 1)
            {
                OnStateChanged?.Invoke(true);
            }

            return handler;
        }

        public bool IsOn()
        {
            return _handlers.Count > 0;
        }

        public void Dispose()
        {
            _handlers.ForEach(handler => handler.RemoveDisposedCallback(OnHandlerDisposed));
            _handlers.Clear();
        }

        void OnHandlerDisposed(MultipleSwitchHandler handler)
        {
            _handlers.Remove(handler);

            // 最後のHandlerがDisposeされたときにOnStateChangedをfalseで呼ぶ
            if (_handlers.Count == 0)
            {
                OnStateChanged?.Invoke(false);
            }
        }
    }
}
