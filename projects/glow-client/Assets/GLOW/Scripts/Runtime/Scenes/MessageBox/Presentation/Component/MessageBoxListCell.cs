using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects.MessageBox;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.MessageBox.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.MessageBox.Presentation.Component
{
    public class MessageBoxListCell : UICollectionViewCell
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct MessageIconImage
        {
            public MessageFormatType _messageFormatType;
            public MessageStatus Status;
            public UIImage IconImage;
        }
        [SerializeField] List<MessageIconImage> _messageIconImageList;
        [SerializeField] UIText _messageTitle;
        [SerializeField] UIText _messageLimitTime;
        [SerializeField] UIText _messageReceivedDate;
        [SerializeField] UIImage _unreadPlateImage;
        [SerializeField] UIImage _openedPlateImage;
        [SerializeField] UIImage _grayOutPlateImage;
        [SerializeField] UIImage _noticeBadgeImage;
        [SerializeField] Button _button;
        
        MessageFormatType _messageFormatType;

        protected override void Awake()
        {
            base.Awake();

            AddButton(_button, "message");
        }

        public void Setup(IMessageBoxCellViewModel viewModel)
        {
            _messageFormatType = viewModel.MessageFormatType;
            _messageTitle.SetText(viewModel.MessageTitle.Value);
            _messageLimitTime.SetText(TimeSpanFormatter.FormatRemaining(viewModel.LimitTime));
            _messageReceivedDate.SetText(viewModel.MessageStartAtDate.ToShortDateString());
            SetIconImage(viewModel.MessageStatus);
            SetPlateImage(viewModel.MessageStatus);
            SetNoticeBadgeImage(viewModel.MessageStatus);
        }
        
        public void SetNoticeBadgeImage(MessageStatus status)
        {
            _noticeBadgeImage.IsVisible = status == MessageStatus.New;
        }
        
        public void SetPlateImage(MessageStatus status)
        {
            switch (_messageFormatType)
            {
                case MessageFormatType.HasReward when status == MessageStatus.Received:
                    _unreadPlateImage.IsVisible = false;
                    _grayOutPlateImage.IsVisible = true;
                    _openedPlateImage.IsVisible = true;
                    break;
                case MessageFormatType.HasNotReward when status == MessageStatus.Opened:
                    _unreadPlateImage.IsVisible = false;
                    _grayOutPlateImage.IsVisible = true;
                    _openedPlateImage.IsVisible = true;
                    break;
                default:
                    _unreadPlateImage.IsVisible = true;
                    _grayOutPlateImage.IsVisible = false;
                    _openedPlateImage.IsVisible = false;
                    break;
            }
        }

        public void SetIconImage(MessageStatus status)
        {
            foreach (var mailIconImage in _messageIconImageList)
            {
                if (mailIconImage._messageFormatType == _messageFormatType && mailIconImage.Status == status)
                {
                    mailIconImage.IconImage.IsVisible = true;
                }
                else
                {
                    mailIconImage.IconImage.IsVisible = false;
                }
            }
        }
    }
}
