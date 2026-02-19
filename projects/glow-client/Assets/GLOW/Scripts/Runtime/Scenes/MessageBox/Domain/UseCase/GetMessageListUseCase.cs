using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.MessageBox;
using GLOW.Core.Extensions;
using GLOW.Scenes.MessageBox.Domain.Definition.Service;
using GLOW.Scenes.MessageBox.Domain.Model;
using GLOW.Scenes.MessageBox.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.MessageBox.Domain.UseCase
{
    public class GetMessageListUseCase
    {
        [Inject] IMessageService MessageService { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMessageCacheRepository MessageCacheRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IOpenedMessagePreferenceRepository OpenedMessagePreferenceRepository { get; }

        public async UniTask<MessageCommonResultUseCaseModel> GetMessageList(CancellationToken cancellationToken)
        {
            var messageResultModel = await MessageService.UpdateAndFetch(cancellationToken);

            if (messageResultModel.Messages.IsEmpty())
            {
                return new MessageCommonResultUseCaseModel(
                    new List<MessageListUseCaseModel>(),
                    false,
                    false);
            }

            var openedMessageIds = OpenedMessagePreferenceRepository.OpenedMessageIds.ToHashSet();

            var list = messageResultModel.Messages.Select(
                message =>
                {
                    var messageFormatType = message.RewardModels.IsEmpty()
                        ? MessageFormatType.HasNotReward
                        : MessageFormatType.HasReward;
                    var messageStatus = GetMessageStatus(
                        message.OpenedAt, 
                        message.ReceivedAt,
                        message.OprMessageId,
                        openedMessageIds);
                    var isActionCompleted = (messageFormatType == MessageFormatType.HasReward && messageStatus == MessageStatus.Received) ||
                                            (messageFormatType == MessageFormatType.HasNotReward && messageStatus == MessageStatus.Opened);

                    var rewards = message.RewardModels.Select(
                            reward => PlayerResourceModelFactory.Create(
                                reward.ResourceType, 
                                reward.ResourceId, 
                                reward.Amount))
                        .ToList();
                    return new MessageListUseCaseModel(
                        message.UserMessageId,
                        message.OprMessageId,
                        messageFormatType,
                        messageStatus,
                        new MessageActionCompletedFlag(isActionCompleted),
                        rewards,
                        message.MessageTitle,
                        message.MessageBody,
                        message.StartAt,
                        message.ExpireAt,
                        message.ExpireAt.IsEmpty() ?
                            RemainingTimeSpan.Infinity : 
                            message.ExpireAt.GetLimitedTimeSpan(TimeProvider.Now)
                    );
                })
                .OrderBy(message => message.IsActionCompleted)
                .ThenBy(message => message.OprId)
                .ToList();

            MessageCacheRepository.SetMessageStatusCacheModel(list);

            var updatedList = MessageCacheRepository.GetMessageListModels();
            var canBulkReceive = updatedList
                .Where(model => model.MessageFormatType == MessageFormatType.HasReward)
                .Any(model => model.MessageStatus != MessageStatus.Received);
            var canBulkOpen = updatedList.Any(model => model.MessageStatus == MessageStatus.New);

            return new MessageCommonResultUseCaseModel(list, canBulkReceive, canBulkOpen);
        }

        MessageStatus GetMessageStatus(
            MessageOpenedAtDate openedAtDate, 
            MessageReceivedDate receivedDate,
            MasterDataId messageId,
            HashSet<MasterDataId> openedMessageIds)
        {
            if (!openedAtDate.IsEmpty() && receivedDate.IsEmpty())
            {
                return MessageStatus.Opened;
            }

            if (openedMessageIds.Contains(messageId))
            {
                return MessageStatus.Opened;
            }

            if (!openedAtDate.IsEmpty() && !receivedDate.IsEmpty())
            {
                return MessageStatus.Received;
            }

            return MessageStatus.New;
        }
    }
}
