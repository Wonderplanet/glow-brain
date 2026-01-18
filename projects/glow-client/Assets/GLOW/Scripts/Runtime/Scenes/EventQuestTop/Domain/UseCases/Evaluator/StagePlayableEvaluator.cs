using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public static class StagePlayableEvaluator
    {
        public static StagePlayableFlag EvaluateNormalStage(
            MstStageModel releaseRequiredMstStageModel,
            IReadOnlyList<IStageClearCountable> stages)
        {
            if (releaseRequiredMstStageModel.IsEmpty()) return StagePlayableFlag.True;
            return new StagePlayableFlag(stages.Exists(s => s.MstStageId == releaseRequiredMstStageModel.Id && 1 <= s.ClearCount));
        }

        public static StageReleaseStatus EvaluateEventStage(
            DateTimeOffset now,
            MstStageModel mstStageModel,
            MstStageEventSettingModel mstSettingModel,
            UserStageEventModel userModel,
            MstStageModel releaseRequiredMstStageModel,
            UserStageEventModel releaseRequiredUserStageModel,
            CampaignModel campaignModel)
        {
            var isRelease = IsRelease(releaseRequiredMstStageModel, releaseRequiredUserStageModel);
            var isPlayable = IsPlayable(mstSettingModel, userModel, campaignModel);

            if (IsReleaseFromDateTime(mstStageModel, now))
            {
                return isRelease && isPlayable
                    ? new StageReleaseStatus(StageStatus.Released)
                    : new StageReleaseStatus(StageStatus.UnRelease);
            }
            else
            {
                //時間開放以外で制約があれば、そちらのステータスが優先される
                if(!isRelease || !isPlayable)
                {
                    return new StageReleaseStatus(StageStatus.UnRelease);
                }
                return new StageReleaseStatus(StageStatus.UnReleaseAtOutOfTime);
            }
        }

        public static bool IsPlayableEvent(
            MstStageEventSettingModel mstSettingModel,
            UserStageEventModel userModel,
            CampaignModel campaignModel)
        {

            return IsPlayable(mstSettingModel, userModel, campaignModel);
        }

        static bool IsReleaseFromDateTime(MstStageModel mstStageModel, DateTimeOffset now)
        {
            return CalculateTimeCalculator.IsValidTime(now, mstStageModel.StartAt, mstStageModel.EndAt);
        }

        static bool IsRelease(
            MstStageModel releaseRequiredMstStageModel,
            UserStageEventModel requiredUserStageModel)
        {
            if (releaseRequiredMstStageModel.IsEmpty())
            {
                return true;
            }

            //前ステージクリアしてるか？
            if (requiredUserStageModel.IsEmpty()) return false;
            return 1 <= requiredUserStageModel.TotalClearCount;
        }

        static bool IsPlayable(
            MstStageEventSettingModel mstSettingModel,
            UserStageEventModel userModel,
            CampaignModel campaignModel)
        {
            // 毎日の上限クリア回数越してないか？
            if (userModel.IsEmpty() || mstSettingModel.ClearableCount.IsEmpty()) return true;
            var clearableCount = CreateClearableCount(mstSettingModel, campaignModel);

            if (mstSettingModel.ResetType != null && mstSettingModel.ResetType.Value == ResetType.Daily)
            {
                //1日1回(ResetType.Daily)だったら日跨ぎresetカウントを見る
                return userModel.ResetClearCount < clearableCount;
            }
            else
            {
                return userModel.TotalClearCount < clearableCount;
            }
        }

        static ClearableCount CreateClearableCount(
            MstStageEventSettingModel mstSettingModel,
            CampaignModel campaignModel)
        {
            var clearableCount = mstSettingModel.ClearableCount;
            if (!campaignModel.IsEmpty() && campaignModel.IsChallengeCountCampaign())
            {
                clearableCount += campaignModel.EffectValue;
            }
            return clearableCount;
        }
    }
}
