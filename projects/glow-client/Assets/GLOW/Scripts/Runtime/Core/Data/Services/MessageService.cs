using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Models.Message;
using GLOW.Scenes.MessageBox.Domain.Definition.Service;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class MessageService : IMessageService
    {
        [Inject] MessageApi MessageApi { get; }

        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }
        
        async UniTask<MessageResultModel> IMessageService.UpdateAndFetch(CancellationToken cancellationToken)
        {
            try
            {
                var messageUpdateAndFetchData = await MessageApi.UpdateAndFetch(cancellationToken);
                return MessageUpdateAndFetchResultTranslator.ToMessageUpdateAndFetchResultData(
                    messageUpdateAndFetchData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<MessageReceiveResultModel> IMessageService.Receive(CancellationToken cancellationToken, IReadOnlyList<string> messageIds)
        {
            try
            {
                var messageReceiveResultData = await MessageApi.Receive(cancellationToken, messageIds.ToArray());
                return MessageReceiveResultTranslator.ToMessageReceiveResultModel(messageReceiveResultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask IMessageService.Open(CancellationToken cancellationToken, IReadOnlyList<string> messageIds)
        {
            await MessageApi.Open(cancellationToken, messageIds.ToArray());
        }
    }
}