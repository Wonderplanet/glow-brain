using System;

namespace GLOW.Modules.Tutorial.Presentation.Views
{
    public class ButtonClickEventListener
    {
        Action _action;
        public ButtonClickEventListener(Action action)
        {
            _action = action;
        }

        public void OnClick()
        {
            _action?.Invoke();
        }
    }
}
