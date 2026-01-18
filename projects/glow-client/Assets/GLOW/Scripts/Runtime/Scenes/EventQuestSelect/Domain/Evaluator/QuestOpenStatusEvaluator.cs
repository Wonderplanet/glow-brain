using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Scenes.EventQuestTop.Domain.UseCases;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.EventQuestSelect.Domain.Evaluator
{
    public class QuestOpenStatusEvaluator : IQuestOpenStatusEvaluator
    {
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IQuestReleaseCheckSampleFinder QuestReleaseCheckSampleFinder { get; }

        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstStageEventSettingDataRepository MstStageEventSettingDataRepository { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }


        QuestOpenStatus IQuestOpenStatusEvaluator.Evaluate(MstQuestModel targetMstQuestModel)
        {
            var releaseTargetMstStage = QuestReleaseCheckSampleFinder.GetSampleAtMstStageModel(targetMstQuestModel);
            // Enhanceは通らない想定。通すのであれば追加で実装する
            var isReleaseRequiredStage = targetMstQuestModel.QuestType == QuestType.Normal
                ? IsClearReleaseRequiredStageAtNormal(releaseTargetMstStage)
                : IsClearReleaseRequiredStageAtEvent(releaseTargetMstStage);

            var isReleaseQuest = IsReleasedQuest(targetMstQuestModel);
            // IsQuestEndedは表示向けにのみ使うので、ここではメソッド呼び出さない。
            // なのでQuestOpenStatus.QuestEndedがreturnされることはない
            var isReleaseStages = IsReleasedQuestFromEventStageLimit(targetMstQuestModel);
            if (!isReleaseQuest) return QuestOpenStatus.NotOpenQuest;
            if (!isReleaseRequiredStage) return QuestOpenStatus.NoClearRequiredStage;
            if (!isReleaseStages) return QuestOpenStatus.NoPlayableStage;
            return QuestOpenStatus.Released;
        }

        // 今はEventQuestListUseCaseElementModelFactoryでしか使っておらず、それ向けの実装になっている
        IReadOnlyList<QuestOpenStatus> IQuestOpenStatusEvaluator.EvaluateGetAllStatus(MstQuestModel targetMstQuestModel)
        {
            var result = new List<QuestOpenStatus>();
            var releaseTargetMstStage = QuestReleaseCheckSampleFinder.GetSampleAtMstStageModel(targetMstQuestModel);
            // Enhanceは通らない想定。通すのであれば追加で実装する
            var isReleaseRequiredStage = targetMstQuestModel.QuestType == QuestType.Normal
                ? IsClearReleaseRequiredStageAtNormal(releaseTargetMstStage)
                : IsClearReleaseRequiredStageAtEvent(releaseTargetMstStage);
            var isReleaseQuest = IsReleasedQuest(targetMstQuestModel);
            var isQuestEnded = IsQuestEnded(targetMstQuestModel);
            var isReleaseStages = IsReleasedQuestFromEventStageLimit(targetMstQuestModel);

            if (!isReleaseQuest) result.Add(QuestOpenStatus.NotOpenQuest);
            if (isQuestEnded) result.Add(QuestOpenStatus.QuestEnded);
            if (!isReleaseRequiredStage) result.Add(QuestOpenStatus.NoClearRequiredStage);
            if (!isReleaseStages) result.Add(QuestOpenStatus.NoPlayableStage);

            if (result.Count <= 0) result.Add(QuestOpenStatus.Released);

            return result;
        }

        bool IsClearReleaseRequiredStageAtNormal(MstStageModel mstStageModel)
        {
            if (mstStageModel.IsEmpty()) return false; // ステージが存在しない場合は要求未達成と想定
            if (mstStageModel.ReleaseRequiredMstStageId.IsEmpty()) return true;
            return GameRepository.GetGameFetch().StageModels
                .Exists(s => 1 <= s.ClearCount.Value && s.MstStageId == mstStageModel.ReleaseRequiredMstStageId);
        }

        bool IsClearReleaseRequiredStageAtEvent(MstStageModel mstStageModel)
        {
            if (mstStageModel.IsEmpty()) return false; // ステージが存在しない場合は要求未達成と想定
            if (mstStageModel.ReleaseRequiredMstStageId.IsEmpty()) return true;
            return GameRepository.GetGameFetch().UserStageEventModels
                .Exists(s => 1 <= s.ClearCount.Value && s.MstStageId == mstStageModel.ReleaseRequiredMstStageId);
        }

        bool IsQuestEnded(MstQuestModel mstQuestModel)
        {
            return mstQuestModel.EndDate < TimeProvider.Now;
        }

        bool IsReleasedQuest(MstQuestModel mstQuestModel)
        {
            return CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                mstQuestModel.StartDate,
                mstQuestModel.EndDate);
        }

        bool IsReleasedQuestFromEventStageLimit(MstQuestModel mstQuestModel)
        {
            if (mstQuestModel.QuestType != QuestType.Event) return true;

            //期間外のものは判定対象外として弾く
            var targetStages = MstStageDataRepository.GetMstStagesFromMstQuestId(mstQuestModel.Id)
                .Where(m => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, m.StartAt, m.EndAt))
                .ToList();
            var stageAndSettings = CreateStageAndSettings(targetStages);
            var stageEventModels = GameRepository.GetGameFetch().UserStageEventModels;

            return stageAndSettings.Any(s => IsEventStagePlayable(stageEventModels, s.m, s.s, mstQuestModel));
        }

        bool IsEventStagePlayable(
            IReadOnlyList<UserStageEventModel> userStageEventModels,
            MstStageModel mstStageModel,
            MstStageEventSettingModel mstStageEventSettingModel,
            MstQuestModel mstQuestModel)
        {
            var userStageEventModel = userStageEventModels
                .FirstOrDefault(x => x.MstStageId == mstStageModel.Id, UserStageEventModel.Empty);

            var campaignModel = CreateCampaignModel(mstQuestModel);

            var isPlayable = StagePlayableEvaluator.IsPlayableEvent(
                mstStageEventSettingModel,
                userStageEventModel,
                campaignModel);

            return isPlayable;
        }

        CampaignModel CreateCampaignModel(MstQuestModel mstQuestModel)
        {
            var campaignQuestType = mstQuestModel.QuestType == QuestType.Event
                ? CampaignTargetType.EventQuest
                : CampaignTargetType.NormalQuest;
            return CampaignModelFactory.CreateCampaignModel(
                mstQuestModel.Id,
                campaignQuestType,
                CampaignTargetIdType.Quest,
                mstQuestModel.Difficulty,
                CampaignType.ChallengeCount);
        }

        IReadOnlyList<(MstStageModel m, MstStageEventSettingModel s)> CreateStageAndSettings(
            IReadOnlyList<MstStageModel> mstStageModels)
        {
            return mstStageModels
                .Join(MstStageEventSettingDataRepository.GetStageEventSettings(),
                    m => m.Id,
                    s => s.MstStageId,
                    (m, s) => (m, s))
                .ToList();
        }
    }
}
