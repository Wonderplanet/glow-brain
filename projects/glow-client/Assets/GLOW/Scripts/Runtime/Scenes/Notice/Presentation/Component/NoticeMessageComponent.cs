using GLOW.Core.Domain.ValueObjects.Notice;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.Notice.Presentation.Component
{
    public class NoticeMessageComponent : UIObject
    {
        [SerializeField] UIText _messageText;

        public void SetupMessageText(NoticeMessage message)
        {
            _messageText.SetText(message.Value);
        }
    }
}