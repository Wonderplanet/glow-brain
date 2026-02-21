using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.MessageBox.Domain.Evaluator
{
    public interface IMessageExpiryEvaluator
    {
        bool CanReceiveMessages(IReadOnlyList<MasterDataId> receiveMessageIds);
    }
}