using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.MessageBox.Domain.Model;

namespace GLOW.Core.Data.Repositories
{
    public class MessageCacheRepository : IMessageCacheRepository
    {
        List<MessageListUseCaseModel> _messageModels;
        
        public void SetMessageStatusCacheModel(List<MessageListUseCaseModel> models)
        {
            _messageModels = models;
        }
        
        public List<MessageListUseCaseModel> GetMessageListModels()
        {
            return _messageModels;
        }

        public void SaveMissionStatus(MasterDataId messageId, MessageStatus messageStatus)
        {
            var updateMessageIndex =
                _messageModels.FindIndex(model =>
                    model.MessageId == messageId);
            if (updateMessageIndex == -1) return;
            
            var updateMessage = _messageModels[updateMessageIndex];
            if (updateMessage.MessageStatus != MessageStatus.Received)
            {
                _messageModels[updateMessageIndex] = updateMessage with { MessageStatus = messageStatus };
            }
        }

        public MessageStatus GetMissionStatus(MasterDataId messageId)
        {
            var updateMessageIndex =
                _messageModels.FindIndex(model =>
                    model.MessageId == messageId);
            if (updateMessageIndex == -1) return MessageStatus.New;
            
            return _messageModels[updateMessageIndex].MessageStatus;
        }

        
    }
}