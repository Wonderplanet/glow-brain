using System;
using GLOW.Modules.MessageView.Presentation.Constants;

namespace GLOW.Modules.MessageView.Presentation
{
    public class UIMessageAction
    {
        public string Title { get; private set; }
        public UIMessageActionStyle Style { get; private set; }

        Action<UIMessageAction> _handler;

        public UIMessageAction(
            string title,
            UIMessageActionStyle style = UIMessageActionStyle.Default,
            Action<UIMessageAction> handler = null)
        {
            this.Title = title;
            this.Style = style;
            this._handler = handler;
        }

        public void Invoke()
        {
            if (_handler != null)
            {
                _handler(this);
            }
        }
    }
}
