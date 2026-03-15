using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.MessageBox.Domain.Evaluator;
using Zenject;

namespace GLOW.Scenes.MessageBox.Domain.UseCase
{
    public class CanReceiveMessageUseCase
    {
        [Inject] IMessageExpiryEvaluator MessageExpiryEvaluator { get; }
        
        public bool IsReceivable(IReadOnlyList<MasterDataId> receiveMessageIds)
        {
            return MessageExpiryEvaluator.CanReceiveMessages(receiveMessageIds);
        }
    }
}