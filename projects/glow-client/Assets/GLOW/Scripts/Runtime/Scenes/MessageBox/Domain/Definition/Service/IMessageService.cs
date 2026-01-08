using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.Message;

namespace GLOW.Scenes.MessageBox.Domain.Definition.Service
{
    public interface IMessageService
    {
        UniTask<MessageResultModel> UpdateAndFetch(CancellationToken cancellationToken);
        
        UniTask<MessageReceiveResultModel> Receive(CancellationToken cancellationToken, IReadOnlyList<string> messageIds);
        
        UniTask Open(CancellationToken cancellationToken, IReadOnlyList<string> messageIds);
    }
}