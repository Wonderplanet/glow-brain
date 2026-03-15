using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.MessageBoxDetail.Presentation.Component;
using GLOW.Scenes.MessageBoxDetail.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.MessageBoxDetail.Presentation.View
{
    public class MessageBoxDetailWithRewardView : UIView
    {
        [SerializeField] UIText _mailTitle;
        [SerializeField] UIText _mailMessage;
        [SerializeField] UIText _dateTimeText;
        [SerializeField] UIText _remainingTimeText;
        [SerializeField] MessageBoxDetailRewardComponent _messageRewardComponent;
        [SerializeField] Button _receiveButton;

        public Action<PlayerResourceIconViewModel> OnPlayerResourceIconCellTapped
        {
            get => _messageRewardComponent.OnPlayerResourceIconTapped;
            set => _messageRewardComponent.OnPlayerResourceIconTapped = value;
        }

        public void SetViewModel(MessageBoxDetailViewModel viewModel)
        {
            _mailTitle.SetText(viewModel.MessageTitle.Value);
            _mailMessage.SetText(viewModel.MessageBody.Value);
            _dateTimeText.SetText(viewModel.MessageStartAtDate.ToShortDateString());
            _remainingTimeText.SetText(TimeSpanFormatter.FormatRemaining(viewModel.LimitTime));
            _messageRewardComponent.Setup(viewModel.RewardList);

            if (viewModel.MessageStatus == MessageStatus.Received || viewModel.LimitTime.Value <= TimeSpan.Zero)
            {
                _receiveButton.interactable = false;
            }
        }
    }
}
