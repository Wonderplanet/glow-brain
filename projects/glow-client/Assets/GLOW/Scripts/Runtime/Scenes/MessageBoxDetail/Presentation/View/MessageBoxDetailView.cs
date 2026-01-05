using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.MessageBoxDetail.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.MessageBoxDetail.Presentation.View
{
    public class MessageBoxDetailView : UIView
    {
        [SerializeField] UIText _mailTitle;
        [SerializeField] UIText _dateTimeText;
        [SerializeField] UIText _remainingTimeText;
        [SerializeField] UIText _mailMessage;
        [SerializeField] Button _readButton;

        public void SetViewModel(MessageBoxDetailViewModel viewModel)
        {
            _mailTitle.SetText(viewModel.MessageTitle.Value);
            _mailMessage.SetText(viewModel.MessageBody.Value);
            _dateTimeText.SetText(viewModel.MessageStartAtDate.ToShortDateString());
            _remainingTimeText.SetText(TimeSpanFormatter.FormatRemaining(viewModel.LimitTime));
            _readButton.interactable = viewModel.MessageStatus == MessageStatus.New;
        }
    }
}
