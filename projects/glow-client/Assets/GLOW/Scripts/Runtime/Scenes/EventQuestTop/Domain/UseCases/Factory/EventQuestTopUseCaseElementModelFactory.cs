using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Scenes.EventQuestTop.Domain.Models;
using GLOW.Scenes.InGameSpecialRule.Domain.Evaluator;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public class EventQuestTopUseCaseElementModelFactory : IEventQuestTopUseCaseElementModelFactory
    {
        [Inject] IMstQuestDataRepository QuestDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstStageEventSettingDataRepository MstStageEventSettingDataRepository { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        [Inject] IUnitListToMstCharacterModelFactory UnitListToMstCharacterModelFactory { get; }
        [Inject] ISpeedAttackUseCaseModelFactory SpeedAttackUseCaseModelFactory { get; }
        [Inject] IArtworkFragmentCompleteEvaluator ArtworkFragmentCompleteEvaluator { get; }
        [Inject] IInGameSpecialRuleEvaluator InGameSpecialRuleEvaluator { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }

        IReadOnlyList<EventQuestTopElementModel> IEventQuestTopUseCaseElementModelFactory.Create(MasterDataId questGroupId)
        {
            var mstQuestModels = QuestDataRepository.GetMstQuestModelsByQuestGroup(questGroupId);
            return CreateStages(mstQuestModels);
        }

        IReadOnlyList<EventQuestTopElementModel> CreateStages(IReadOnlyList<MstQuestModel> models)
        {
            //mstQuestGroupId同じやつは全部出す
            return models.SelectMany(CreateStagesByMstQuestModel).ToList();
        }

        IReadOnlyList<EventQuestTopElementModel> CreateStagesByMstQuestModel(MstQuestModel model)
        {
            var targetStages = MstStageDataRepository.GetMstStagesFromMstQuestId(model.Id);
            var stageAndSettings = CreateStageAndSettings(model.QuestType, targetStages);

            var stageEventModels = GameRepository.GetGameFetch().UserStageEventModels;
            var currentPartyMstCharacterModels = UnitListToMstCharacterModelFactory.CreateFromCurrentParty();
            var completeFlags = CreateStageArtworkFragmentCompleteFlags(targetStages);
            return stageAndSettings
                .Select(stageAndSetting => CreateElement(
                    stageEventModels,
                    stageAndSetting.m,
                    stageAndSetting.s,
                    currentPartyMstCharacterModels,
                    completeFlags,
                    model))
                .ToList();
        }

        IReadOnlyList<(MstStageModel m, MstStageEventSettingModel s)>
            CreateStageAndSettings(QuestType questType,IReadOnlyList<MstStageModel> mstStageModels)
        {
            return mstStageModels
                .Join(MstStageEventSettingDataRepository.GetStageEventSettings(),
                    m => m.Id,
                    s => s.MstStageId,
                    (m, s) => (m, s))
                .ToList();
        }


        EventQuestTopElementModel CreateElement(
            IReadOnlyList<UserStageEventModel> userStageEventModels,
            MstStageModel mstStageModel,
            MstStageEventSettingModel mstStageEventSettingModel,
            IReadOnlyList<MstCharacterModel> mstCharacterModels,
            IReadOnlyList<(MasterDataId mstArtworkFragmentDropGroupId, StageRewardCompleteFlag completeArtworkFragment)> completeFlags,
            MstQuestModel mstQuestModel
        )
        {
            var prevMstStage = MstStageDataRepository.GetMstStageFirstOrDefault(mstStageModel.ReleaseRequiredMstStageId);
            var prevUserModel = prevMstStage.IsEmpty()
                ? UserStageEventModel.Empty
                : userStageEventModels
                    .FirstOrDefault(m => m.MstStageId == prevMstStage.Id, UserStageEventModel.Empty);

            var userStageEventModel =
                userStageEventModels.FirstOrDefault(x => x.MstStageId == mstStageModel.Id, UserStageEventModel.Empty);

            var campaignModel = CampaignModelFactory.CreateCampaignModel(
                mstQuestModel.Id,
                CampaignTargetType.EventQuest,
                CampaignTargetIdType.Quest,
                mstQuestModel.Difficulty,
                CampaignType.ChallengeCount);

            var stageReleaseStatus = StagePlayableEvaluator.EvaluateEventStage(
                TimeProvider.Now,
                mstStageModel,
                mstStageEventSettingModel,
                userStageEventModel,
                prevMstStage,
                prevUserModel,
                campaignModel);

            var speedAttackUseCaseModel = SpeedAttackUseCaseModelFactory.Create(userStageEventModel);
            var isSpeedAttack = SpeedAttackRewardCompleteEvaluator.Evaluate(speedAttackUseCaseModel);

            var mstInGameSpecialRuleModels =
                MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(mstStageModel.Id, InGameContentType.Stage);

            var isFragmentComplete = mstStageModel.MstArtworkFragmentDropGroupId.IsEmpty()
                ? StageRewardCompleteFlag.False //かけらない...False
                : completeFlags
                    .First(c =>
                        c.mstArtworkFragmentDropGroupId == mstStageModel.MstArtworkFragmentDropGroupId).completeArtworkFragment;

            var consumeStamina = mstStageModel.StageConsumeStamina;
            if (!campaignModel.IsEmpty() && campaignModel.IsStaminaCampaign())
            {
                consumeStamina = StageStaminaCalculator.CalcConsumeStaminaInCampaign(
                    mstStageModel.StageConsumeStamina,
                    campaignModel.EffectValue);
            }

            var clearableCount = mstStageEventSettingModel.ClearableCount;
            if (!campaignModel.IsEmpty() && campaignModel.IsChallengeCountCampaign())
            {
                clearableCount += campaignModel.EffectValue;
            }

            ExistsSpecialRuleFlag existsSpecialRuleFlag = ExistsSpecialRuleFlag.False;
            if (stageReleaseStatus.Value == StageStatus.Released)
            {
                existsSpecialRuleFlag = InGameSpecialRuleEvaluator.ExistsSpecialRule(
                    InGameContentType.Stage,
                    mstStageModel.Id,
                    QuestType.Event);
            }

            var stageClear = StageClearStatusEvaluator.Evaluate(
                mstStageEventSettingModel,
                userStageEventModel,
                stageReleaseStatus.Value == StageStatus.Released);

            var staminaBoostBalloonType = stageReleaseStatus.Value != StageStatus.Released
                ? StaminaBoostBalloonType.None
                : mstStageModel.AutoLapType switch
                {
                    AutoLapType.Initial => StaminaBoostBalloonType.DefaultBalloon,
                    AutoLapType.AfterClear when stageClear == StageClearStatus.Clear => StaminaBoostBalloonType.DefaultBalloon,
                    AutoLapType.AfterClear => StaminaBoostBalloonType.FirstClearBalloon,
                    _ => StaminaBoostBalloonType.None
                };

            return new EventQuestTopElementModel(
                mstStageModel.Id,
                mstStageModel.StageNumber,
                mstStageModel.RecommendedLevel,
                StageIconAssetPath.FromAssetKey(mstStageModel.StageAssetKey),
                mstStageModel.Name,
                consumeStamina,
                prevMstStage.IsEmpty() ? StageNumber.Empty : prevMstStage.StageNumber,
                stageReleaseStatus,
                stageClear,
                new UnlimitedCalculableDateTimeOffset(mstStageModel.EndAt),
                userStageEventModel.IsEmpty() ? StageClearCount.Empty : userStageEventModel.ResetClearCount,
                clearableCount,
                speedAttackUseCaseModel,
                isFragmentComplete,
                IsStageRewardComplete(isSpeedAttack, speedAttackUseCaseModel.NextGoalTime),
                InGameSpecialRuleAchievingEvaluator.CreateAchievedSpecialRuleFlag(mstCharacterModels, mstInGameSpecialRuleModels),
                existsSpecialRuleFlag,
                CreateStageReleaseRequireSentence(
                    stageReleaseStatus.Value,
                    mstStageModel.StartAt,
                    prevMstStage.StageNumber
                ),
                KomaBackgroundAssetPath.FromAssetKey(mstStageEventSettingModel.EventTopBackGroundAssetKey),
                staminaBoostBalloonType
            );
        }

        StageReleaseRequireSentence CreateStageReleaseRequireSentence(
            StageStatus stageStatus,
            DateTimeOffset stageStartAt,
            StageNumber releaseRequiredStageNumber)
        {
            if(stageStatus ==StageStatus.Released) return StageReleaseRequireSentence.Empty;
            if (stageStatus == StageStatus.UnRelease)
            {
                return StageReleaseRequireSentence.CreateReleaseRequiredSentence(releaseRequiredStageNumber.Value);
            }
            else return StageReleaseRequireSentence.CreateOpenLimitSentence(stageStartAt);
        }

        IReadOnlyList<(MasterDataId mstArtworkFragmentDropGroupId, StageRewardCompleteFlag completeArtworkFragment)>
            CreateStageArtworkFragmentCompleteFlags(IReadOnlyList<MstStageModel> mstStageModels)
        {
            return mstStageModels
                .Distinct(m => m.MstArtworkFragmentDropGroupId)
                .Where(m => !m.MstArtworkFragmentDropGroupId.IsEmpty())
                .Select(m => (
                        m.MstArtworkFragmentDropGroupId,
                        ArtworkFragmentCompleteEvaluator.Evaluate(m.MstArtworkFragmentDropGroupId))
                )
                .ToList();
        }

        StageRewardCompleteFlag IsStageRewardComplete(StageRewardCompleteFlag isSpeedAttack, StageClearTime nextGoalTime)
        {
            return new StageRewardCompleteFlag(isSpeedAttack && nextGoalTime.IsEmpty());
        }
    }
}
