using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Modules.TutorialTipDialog.Domain.Definitions;
using GLOW.Scenes.BoxGacha.Domain.Evaluator;
using GLOW.Scenes.EventQuestSelect.Domain.Factory;
using GLOW.Scenes.EventQuestSelect.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.EventQuestSelect.Domain
{
    public class EventQuestListUseCase
    {
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IEventQuestListUseCaseElementModelFactory EventQuestListUseCaseElementModelFactory { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IAdventBattleOpenStatusEvaluator AdventBattleOpenStatusEvaluator { get; }
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }
        [Inject] IMstBoxGachaDataRepository MstBoxGachaDataRepository { get; }

        public EventQuestListUseCaseModel GetModel(MasterDataId mstEventId)
        {
            var mstEvent = MstEventDataRepository.GetEvent(mstEventId);
            var mstAdventBattle = MstAdventBattleDataRepository
                .GetMstAdventBattleModels()
                .FirstOrDefault(m => m.MstEventId == mstEventId, MstAdventBattleModel.Empty);

            var remainingTime = mstEvent.EndAt - TimeProvider.Now;

            var campaignModels = GetCampaignModels(mstEventId);

            var boxGachaModel = MstBoxGachaDataRepository.GetMstBoxGachaModelByMstEventIdFirstOrDefault(mstEventId);

            return new EventQuestListUseCaseModel(
                mstEventId,
                CreateAdventBattleModel(mstAdventBattle),
                mstEvent.AssetKey,
                new RemainingTimeSpan(remainingTime),
                mstEvent.EndAt,
                EventQuestListUseCaseElementModelFactory.Create(mstEventId),
                GetEventCampaignRemainingTimeSpan(campaignModels),
                boxGachaModel.Id);
        }

        List<CampaignModel> GetCampaignModels(MasterDataId mstEventId)
        {
            var quests = MstQuestDataRepository.GetMstQuestModelsFromEvent(mstEventId);

            var result = new List<CampaignModel>();
            foreach (var quest in quests)
            {
                var campaignModels = CampaignModelFactory.CreateCampaignModels(
                    quest.Id,
                    CampaignTargetType.EventQuest,
                    CampaignTargetIdType.Quest,
                    quest.Difficulty);
                result.AddRange(campaignModels);
            }
            return result;
        }

        EventQuestListAdventBattleModel CreateAdventBattleModel(
            MstAdventBattleModel mstAdventBattle)
        {
            var adventBattleOpenStatus = AdventBattleOpenStatusEvaluator.Evaluate(mstAdventBattle);
            var adventBattleTutorialModel = MstTutorialRepository.GetMstTutorialModels()
                .FirstOrDefault(m => m.TutorialFunctionName == HelpDialogIdDefinitions.AdventBattle,
                    MstTutorialModel.Empty);
            var releaseRequiredUserLevel = adventBattleTutorialModel.IsEmpty() ?
                UserLevel.Empty :
                adventBattleTutorialModel.ConditionValue.ToUserLevel();

            var timeSpan = CreateAdventBattleTimeSpan(adventBattleOpenStatus, mstAdventBattle);
            return new EventQuestListAdventBattleModel(
                mstAdventBattle.Id,
                adventBattleOpenStatus,
                timeSpan,
                mstAdventBattle.AdventBattleName,
                releaseRequiredUserLevel);
        }

        RemainingTimeSpan CreateAdventBattleTimeSpan(AdventBattleOpenStatus status, MstAdventBattleModel model)
        {
            //開催前は開催までの時間
            //開催中は終了までの時間
            return status.Value switch
            {
                AdventBattleOpenStatusType.BeforeOpened => new RemainingTimeSpan(model.StartDateTime.Value - TimeProvider.Now),
                AdventBattleOpenStatusType.Opened => new RemainingTimeSpan(model.EndDateTime.Value - TimeProvider.Now),
                _ => RemainingTimeSpan.Empty
            };
        }

        RemainingTimeSpan GetEventCampaignRemainingTimeSpan(List<CampaignModel> campaignModels)
        {
            return campaignModels.MaxBy(model => model.RemainingTimeSpan.Value)
                ?.RemainingTimeSpan ?? RemainingTimeSpan.Empty;
        }
    }
}
