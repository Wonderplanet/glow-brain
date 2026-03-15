using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.EnhanceQuestTop.Domain.Factories;
using GLOW.Scenes.EnhanceQuestTop.Domain.Models;
using GLOW.Scenes.PassShop.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.EnhanceQuestTop.Domain.UseCases
{
    public class EnhanceQuestTopUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IMstStageEnhanceRewardParamDataRepository MstStageEnhanceRewardParamDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstQuestEventBonusScheduleDataRepository MstQuestEventBonusScheduleDataRepository { get; }
        [Inject] IHeldAdSkipPassInfoModelFactory HeldAdSkipPassInfoModelFactory { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }
        [Inject] IQuestPartyModelFactory QuestPartyModelFactory { get; }
        [Inject] IEnhanceQuestModelFactory EnhanceQuestModelFactory { get; }

        public EnhanceQuestTopModel GetEnhanceQuestTop()
        {
            var userStageEnhance = GameRepository.GetGameFetch().UserStageEnhanceModels.FirstOrDefault();

            var enhanceQuestModel = EnhanceQuestModelFactory.CreateCurrentEnhanceQuestModel();
            var mstQuestId = enhanceQuestModel.MstQuest.Id;
            var mstStageId = enhanceQuestModel.MstStage.Id;
            var difficulty = enhanceQuestModel.MstQuest.Difficulty;

            // パーティ情報
            var currentParty = PartyCacheRepository.GetCurrentPartyModel();
            var questPartyModel = QuestPartyModelFactory.Create(currentParty, mstQuestId);

            var partyName = currentParty.PartyName;
            var totalBonusPercentage = questPartyModel.TotalBonusPercentage;

            var campaignModels = CampaignModelFactory.CreateCampaignModels(
                mstQuestId,
                CampaignTargetType.EnhanceQuest,
                CampaignTargetIdType.Quest,
                difficulty);

            // 挑戦回数
            var challengeCount = MstConfigRepository.GetConfig(MstConfigKey.EnhanceQuestChallengeLimit).Value.ToInt();
            var campaignModel = campaignModels.FirstOrDefault(mst => mst.IsChallengeCountCampaign(), CampaignModel.Empty);
            if (!campaignModel.IsEmpty())
            {
                challengeCount += campaignModel.EffectValue.Value;
            }
            var challengeAdCount = MstConfigRepository.GetConfig(MstConfigKey.EnhanceQuestChallengeAdLimit).Value.ToInt();
            var userChallengeCount = new EnhanceQuestChallengeCount(challengeCount);
            var userAdChallengeCount = new EnhanceQuestChallengeCount(challengeAdCount);
            if (userStageEnhance != null)
            {
                userChallengeCount -= userStageEnhance.ResetChallengeCount;
                userAdChallengeCount -= userStageEnhance.ResetAdChallengeCount;
            }

            // スコア
            var highScore = userStageEnhance?.MaxScore ?? EnhanceQuestScore.Zero;
            var nextBonusThreshold = MstStageEnhanceRewardParamDataRepository.GetStageEnhanceRewardParams()
                .Where(mst => mst.MinThresholdScore > highScore)
                .DefaultIfEmpty(MstStageEnhanceRewardParamModel.Empty)
                .OrderBy(mst => mst.MinThresholdScore)
                .First();

            // イベントボーナス周りの対応
            EventBonusGroupId eventBonusGroupId = EventBonusGroupId.Empty;
            var now = TimeProvider.Now;
            var bonusSchedule = MstQuestEventBonusScheduleDataRepository.GetQuestEventBonusSchedules(mstQuestId)
                .Where(mst => CalculateTimeCalculator.IsValidTime(now, mst.StartAt, mst.EndAt))
                .DefaultIfEmpty(MstQuestEventBonusScheduleModel.Empty)
                .OrderByDescending(mst => mst.StartAt)
                .First();

            // 有効なスケジュール設定のない場合はボーナスなし
            if(!bonusSchedule.IsEmpty()) eventBonusGroupId = bonusSchedule.EventBonusGroupId;

            var heldAdSkipPassInfoModel = HeldAdSkipPassInfoModelFactory.CreateHeldAdSkipPassInfo();

            return new EnhanceQuestTopModel(
                mstStageId,
                mstQuestId,
                highScore,
                nextBonusThreshold.MinThresholdScore,
                nextBonusThreshold.CoinRewardAmount,
                userChallengeCount,
                userAdChallengeCount,
                totalBonusPercentage,
                partyName,
                eventBonusGroupId,
                heldAdSkipPassInfoModel,
                campaignModels);
        }
    }
}
