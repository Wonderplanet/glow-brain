using System;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Scenes.StaminaBoostDialog.Domain.Evaluator;
using GLOW.Scenes.StaminaBoostDialog.Domain.Model;
using GLOW.Scenes.StaminaRecover.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.StaminaBoostDialog.Domain.UseCase
{
    public class StaminaBoostUseCase
    {
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstStageEventSettingDataRepository MstStageEventSettingDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IUserStaminaModelFactory UserStaminaModelFactory { get; }
        [Inject] IStaminaBoostEvaluator StaminaBoostEvaluator { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }

        public StaminaBoostDialogModel GetStaminaBoostDialogModel(MasterDataId stageId)
        {
            var mstStageData = MstStageDataRepository.GetMstStage(stageId);
            var userStaminaModel = UserStaminaModelFactory.Create();

            // mstStageDataからスタミナブースト可能回数の上限を取得
            var staminaBoostCountLimit = StaminaBoostEvaluator.GetStaminaBoostCountLimit(mstStageData);
            var maxCountByStamina =  userStaminaModel.MaxStamina.Value / mstStageData.StageConsumeStamina.Value;
            var maxCount = Math.Min(staminaBoostCountLimit.Value, Math.Max(0, maxCountByStamina));

            var mstStageEventSettingModel = MstStageEventSettingDataRepository.GetStageEventSettingFirstOrDefault(stageId);
            var mstQuestModel = MstQuestDataRepository.GetMstQuestModel(mstStageData.MstQuestId);

            // イベントステージのクリア可能回数を取得
            var clearableCount = GetEventClearableCountWithCampaign(mstStageEventSettingModel, mstQuestModel);
            if (clearableCount.Value > 0)
            {
                var userStageEventModels = GameRepository.GetGameFetch().UserStageEventModels;
                var userStageEventModel =
                    userStageEventModels.FirstOrDefault(x => x.MstStageId == stageId, UserStageEventModel.Empty);
                // 既にクリアした回数
                var stageClearCount = userStageEventModel.IsEmpty() ? StageClearCount.Empty : userStageEventModel.ResetClearCount;
                var boostableCount = clearableCount.Value - stageClearCount.Value;
                maxCount = Math.Min(maxCount, Math.Max(0, boostableCount));
            }

            // 消費スタミナ取得
            var consumeStamina = GetConsumeStaminaWithCampaign(mstStageData, mstQuestModel);

            // 万が一0になる可能性がある場合は1にする
            if (maxCount == 0) maxCount = 1;

            return new StaminaBoostDialogModel(
                userStaminaModel.CurrentStamina,
                consumeStamina,
                new StaminaBoostCount(maxCount),
                StaminaIconAssetPath.FromAssetKey(new StaminaAssetKey())
            );
        }

        ClearableCount GetEventClearableCountWithCampaign(
            MstStageEventSettingModel mstStageEventSettingModel,
            MstQuestModel mstQuestModel)
        {
            var clearableCount = mstStageEventSettingModel.ClearableCount;

            // キャンペーン考慮
            var campaignTargetType = mstQuestModel.QuestType == QuestType.Event ? CampaignTargetType.EventQuest : CampaignTargetType.NormalQuest;
            var challengeCountCampaignModel = CampaignModelFactory.CreateCampaignModel(
                mstQuestModel.Id,
                campaignTargetType,
                CampaignTargetIdType.Quest,
                mstQuestModel.Difficulty,
                CampaignType.ChallengeCount);

            if (!challengeCountCampaignModel.IsEmpty())
            {
                clearableCount += challengeCountCampaignModel.EffectValue;
            }

            return clearableCount;
        }

        StageConsumeStamina GetConsumeStaminaWithCampaign(MstStageModel mstStageModel, MstQuestModel mstQuestModel)
        {
            // キャンペーン考慮
            var campaignTargetType = mstQuestModel.QuestType == QuestType.Event ? CampaignTargetType.EventQuest : CampaignTargetType.NormalQuest;
            var staminaCampaignModel = CampaignModelFactory.CreateCampaignModel(
                mstQuestModel.Id,
                campaignTargetType,
                CampaignTargetIdType.Quest,
                mstQuestModel.Difficulty,
                CampaignType.Stamina);

            var consumeStamina = mstStageModel.StageConsumeStamina;
            if (!staminaCampaignModel.IsEmpty())
            {
                consumeStamina = StageStaminaCalculator.CalcConsumeStaminaInCampaign(
                    mstStageModel.StageConsumeStamina,
                    staminaCampaignModel.EffectValue);
            }

            return consumeStamina;
        }
    }
}
