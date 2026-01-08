using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.MessageBox;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.MessageBox.Presentation.ViewModel
{
    public class MessageBoxCellViewModel : IMessageBoxCellViewModel
    {
        public MasterDataId MessageId { get; }
        public MessageFormatType MessageFormatType { get; }
        public MessageStatus MessageStatus { get; }
        public IReadOnlyList<PlayerResourceIconViewModel> MessageRewards { get; }
        public MessageTitle MessageTitle { get; }
        public MessageBody MessageBody { get; }
        public MessageStartAtDate MessageStartAtDate { get; }
        public RemainingTimeSpan LimitTime { get; }
        
        public MessageBoxCellViewModel(MasterDataId messageId, MessageFormatType messageFormatType, MessageStatus messageStatus, IReadOnlyList<PlayerResourceIconViewModel> messageRewards, MessageTitle messageTitle, MessageBody messageBody, MessageStartAtDate messageStartAtDate, RemainingTimeSpan limitTime)
        {
            MessageId = messageId;
            MessageFormatType = messageFormatType;
            MessageStatus = messageStatus;
            MessageRewards = messageRewards;
            MessageTitle = messageTitle;
            MessageBody = messageBody;
            MessageStartAtDate = messageStartAtDate;
            LimitTime = limitTime;
        }
    }
}