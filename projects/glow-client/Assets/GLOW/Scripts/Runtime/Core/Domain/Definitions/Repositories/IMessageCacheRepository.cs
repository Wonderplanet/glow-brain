using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.MessageBox.Domain.Model;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMessageCacheRepository
    {
        void SetMessageStatusCacheModel(List<MessageListUseCaseModel> models);
        List<MessageListUseCaseModel> GetMessageListModels();
        void SaveMissionStatus(MasterDataId messageId, MessageStatus messageStatus);
        MessageStatus GetMissionStatus(MasterDataId messageId);
    }
}