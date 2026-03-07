using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.MessageBox;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.MessageBox.Presentation.ViewModel
{
    public interface IMessageBoxCellViewModel
    {
        public MasterDataId MessageId { get; }
        public MessageFormatType MessageFormatType { get; }
        public MessageStatus MessageStatus { get; }
        public IReadOnlyList<PlayerResourceIconViewModel> MessageRewards { get; }
        public MessageTitle MessageTitle { get; }
        public MessageBody MessageBody { get; }
        MessageStartAtDate MessageStartAtDate { get; }
        public RemainingTimeSpan LimitTime { get; }
    }
}