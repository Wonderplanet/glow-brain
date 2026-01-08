using GLOW.Core.Presentation.Components;
using GLOW.Modules.MessageView.Presentation.Constants;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Modules.MessageView.Presentation
{
    public class MessageView : UIView
    {
        [SerializeField] UIText _titleText;
        [SerializeField] UIText _descriptionMessageText;
        [SerializeField] UIText _attentionMessageText;
        [SerializeField] Transform _actionsLayout;
        [SerializeField] UITextButton _applyButton;
        [SerializeField] UITextButton _cancelButton;

        public UIText TitleText => _titleText;
        public UIText DescriptionMessageText => _descriptionMessageText;
        public UIText AttentionMessageText => _attentionMessageText;

        protected override void Awake()
        {
            base.Awake();

            _actionsLayout.gameObject.SetActive(false);
            _applyButton.gameObject.SetActive(false);
            _cancelButton.gameObject.SetActive(false);
        }

        public Button.ButtonClickedEvent AddActionButton(string title, UIMessageActionStyle style)
        {
            _actionsLayout.gameObject.SetActive(true);

            UITextButton templete;

            switch (style)
            {
                case UIMessageActionStyle.Cancel: templete = _cancelButton; break;
                default: templete = _applyButton; break;
            }

            var button = Instantiate(templete, _actionsLayout, false);

            button.TitleText.SetText(title);
            button.gameObject.SetActive(true);

            return button.onClick;
        }
    }
}
