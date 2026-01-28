using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.MessageBox;
using GLOW.Scenes.MessageBox.Domain.Model;
using Zenject;

namespace GLOW.Scenes.MessageBox.Domain.UseCase
{
    public class OpenMessageUseCase
    {
        [Inject] IOpenedMessagePreferenceRepository OpenedMessagePreferenceRepository { get; }
        [Inject] IMessageCacheRepository MessageCacheRepository { get; }
        
        public MessageCommonResultUseCaseModel OpenAndUpdateSingleMessage(MasterDataId messageId)
        {
            MessageCacheRepository.SaveMissionStatus(messageId, MessageStatus.Opened);
            
            // 既読済みのメッセージIDを端末に保存
            AddOpenedMessageId(messageId);
            
            var updatedList = MessageCacheRepository.GetMessageListModels();
            
            var canBulkReceive = updatedList
                .Where(model => model.MessageFormatType == MessageFormatType.HasReward)
                .Any(model => model.MessageStatus != MessageStatus.Received);
            
            var canBulkOpen = updatedList.Any(model => model.MessageStatus == MessageStatus.New);

            return new MessageCommonResultUseCaseModel(updatedList, canBulkReceive, canBulkOpen);
        }

        void AddOpenedMessageId(MasterDataId messageId)
        {
            var openedMessageIds = OpenedMessagePreferenceRepository.OpenedMessageIds;
            openedMessageIds.Add(messageId);
            OpenedMessagePreferenceRepository.SetOpenedMessageIds(openedMessageIds.Distinct().ToList());
        }
    }
}