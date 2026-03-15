using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.MessageBox;
using GLOW.Core.Extensions;
using GLOW.Scenes.MessageBox.Domain.Definition.Service;
using GLOW.Scenes.MessageBox.Domain.Model;
using Zenject;

namespace GLOW.Scenes.MessageBox.Domain.UseCase
{
    public class BulkOpenMessageUseCase
    {
        [Inject] IMessageService MessageService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMessageCacheRepository MessageCacheRepository { get; }
        [Inject] IOpenedMessagePreferenceRepository OpenedMessagePreferenceRepository { get; }
        
        public async UniTask<MessageCommonResultUseCaseModel> OpenAndUpdateMessages(
            CancellationToken cancellationToken, 
            IReadOnlyList<MasterDataId> messageIds)
        {
            var openedMessageIds = messageIds;
            if (openedMessageIds.IsEmpty())
            {
                // 引数のIDが空の場合は端末に保存されている既読済みのメッセージIDを取得
                openedMessageIds = OpenedMessagePreferenceRepository.OpenedMessageIds;
                
                // 端末に保存されている既読済みのメッセージIDをクリア(サーバー的に全て既読済みとなるため端末保持が不要になる)
                OpenedMessagePreferenceRepository.ClearOpenedMessageIds();
            }

            if (openedMessageIds.IsEmpty()) return MessageCommonResultUseCaseModel.Empty;
            
            await MessageService.Open(cancellationToken, openedMessageIds.Select(id => id.ToString()).ToList());
            
            // ステータス更新(副作用あり)
            foreach (var messageId in openedMessageIds)
            {
                MessageCacheRepository.SaveMissionStatus(messageId, MessageStatus.Opened);
            }
            
            // 既読済みでも報酬が受け取っていないメッセージがある場合があるのでそのメッセージの数を取得する
            var updatedList = MessageCacheRepository.GetMessageListModels();
            var unopenedMessageCount = updatedList.Count(model => model.MessageStatus == MessageStatus.New);
            
            // 副作用あり
            SaveUpdatedGameFetchModel(unopenedMessageCount);
            
            var canBulkReceive = updatedList
                .Where(model => model.MessageFormatType == MessageFormatType.HasReward)
                .Any(model => model.MessageStatus != MessageStatus.Received);
            var canBulkOpen = updatedList.Any(model => model.MessageStatus == MessageStatus.New);
            
            return new MessageCommonResultUseCaseModel(updatedList, canBulkReceive, canBulkOpen);
        }
        
        void SaveUpdatedGameFetchModel(int unopenedMessageCount)
        {
            var fetchModel = GameRepository.GetGameFetch();

            var badge = fetchModel.BadgeModel;
            var updatedFetchModel = fetchModel with
            {
                BadgeModel = badge with
                {
                    UnopenedMessageCount = new UnopenedMessageCount(Math.Max(unopenedMessageCount, 0))
                }
            };
            
            GameManagement.SaveGameFetch(updatedFetchModel);
        }
        
    }
}