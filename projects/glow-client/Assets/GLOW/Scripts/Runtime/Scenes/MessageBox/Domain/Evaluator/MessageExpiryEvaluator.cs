using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.MessageBox.Domain.Evaluator
{
    public class MessageExpiryEvaluator : IMessageExpiryEvaluator
    {
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMessageCacheRepository MessageCacheRepository { get; }
        
        bool IMessageExpiryEvaluator.CanReceiveMessages(IReadOnlyList<MasterDataId> receiveMessageIds)
        {
            if (receiveMessageIds.Count == 0) return false;
            
            var messageIdLSet = receiveMessageIds.ToHashSet();
            var receiveMessageList = MessageCacheRepository.GetMessageListModels()
                .Where(model => messageIdLSet.Contains(model.MessageId));
            
            if (receiveMessageList.Any(model => model.IsExpired(TimeProvider.Now)))
            {
                // 期限切れのメッセージがある場合は受け取れない
                return false;
            }

            return true;
        }
    }
}