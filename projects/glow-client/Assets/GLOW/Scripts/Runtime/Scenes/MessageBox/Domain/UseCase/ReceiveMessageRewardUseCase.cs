using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Message;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.MessageBox;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.MessageBox.Domain.Definition.Service;
using GLOW.Scenes.MessageBox.Domain.Model;
using Zenject;

namespace GLOW.Scenes.MessageBox.Domain.UseCase
{
    public class ReceiveMessageRewardUseCase
    {
        [Inject] IMessageService MessageService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IUserLevelUpCacheRepository UserLevelUpCacheRepository { get; }
        [Inject] IMessageCacheRepository MessageCacheRepository { get; }

        public async UniTask<MessageReceiveResultUseCaseModel> ReceiveMessageReward(
            CancellationToken cancellationToken,
            IReadOnlyList<MasterDataId> messageIds)
        {
            var resultModel = await MessageService.Receive(cancellationToken, messageIds.Select(id => id.ToString()).ToList());

            // ステータス更新
            foreach (var messageId in messageIds)
            {
                MessageCacheRepository.SaveMissionStatus(messageId, MessageStatus.Received);
            }

            var prevUserParameterModel = GameRepository.GetGameFetch().UserParameterModel;

            SaveUpdatedGameFetchModel(resultModel, messageIds.Count);

            // 副作用
            // 経験値を受け取れる関係でレベルアップする可能性があるため
            UserLevelUpCacheRepository.Save(
                resultModel.UserLevel,
                prevUserParameterModel.Level,
                prevUserParameterModel.Exp);

            var updatedList = MessageCacheRepository.GetMessageListModels();

            var canBulkReceive = updatedList
                .Where(model => model.MessageFormatType == MessageFormatType.HasReward)
                .Any(model => model.MessageStatus != MessageStatus.Received);
            
            var canBulkOpen = updatedList.Any(model => model.MessageStatus == MessageStatus.New);

            var commonResult = new MessageCommonResultUseCaseModel(updatedList, canBulkReceive, canBulkOpen);

            return new MessageReceiveResultUseCaseModel(
                CreateCommonReceiveResourceModels(resultModel.RewardModels),
                commonResult);
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveResourceModels(IReadOnlyList<RewardModel> models)
        {
            return models.Select(m =>
                    new CommonReceiveResourceModel(
                        m.UnreceivedRewardReasonType,
                        PlayerResourceModelFactory.Create(m.ResourceType, m.ResourceId, m.Amount),
                        PlayerResourceModelFactory.Create(m.PreConversionResource))
                )
                .ToList();
        }

        void SaveUpdatedGameFetchModel(MessageReceiveResultModel resultModel, int receivedMessageCount)
        {
            var fetchModel = GameRepository.GetGameFetch();
            var fetchOtherModel = GameRepository.GetGameFetchOther();

            var badge = fetchModel.BadgeModel;
            var unopenedMessageCount =
                new UnopenedMessageCount(
                    Math.Max(badge.UnopenedMessageCount.Value - receivedMessageCount,
                        0));
            var updatedFetchModel = fetchModel with
            {
                UserParameterModel = resultModel.UserParameterModel,
                BadgeModel = badge with { UnopenedMessageCount = unopenedMessageCount }
            };

            var updatedFetchOtherModel = fetchOtherModel with
            {
                UserUnitModels = fetchOtherModel.UserUnitModels.Update(resultModel.UserUnits),
                UserItemModels = fetchOtherModel.UserItemModels.Update(resultModel.UserItems),
                UserEmblemModel = fetchOtherModel.UserEmblemModel.Update(resultModel.UserEmblems),
                UserConditionPackModels = fetchOtherModel.UserConditionPackModels.Update(resultModel.UserConditionPacks),
            };

            GameManagement.SaveGameUpdateAndFetch(updatedFetchModel, updatedFetchOtherModel);
        }
    }
}
