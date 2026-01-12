using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Extensions;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.EnhanceQuestTop.Domain.Factories;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.PassShop.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Evaluator
{
    public class InGameRetryEvaluator : IInGameRetryEvaluator
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ISelectedStageEvaluator SelectedStageEvaluator { get; }
        [Inject] IEnhanceQuestModelFactory EnhanceQuestModelFactory { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageEventSettingDataRepository MstStageEventSettingDataRepository { get; }
        [Inject] IHeldAdSkipPassInfoModelFactory HeldAdSkipPassInfoModelFactory { get; }

        public InGameRetryModel DetermineRetryAvailableFlag()
        {
            var selectedStage = SelectedStageEvaluator.GetSelectedStage();
            var selectedId = selectedStage.SelectedId;

            switch (selectedStage.InGameType)
            {
                case InGameType.Normal:
                    return NormalRetryAvailable(selectedId);
                case InGameType.AdventBattle:
                    return AdventBattleRetryAvailable();
                case InGameType.Pvp:
                    return PvpRetryAvailable();
                default:
                    return InGameRetryModel.Empty;
            }
        }

        InGameRetryModel NormalRetryAvailable(MasterDataId mstStageId)
        {
            var mstStage = MstStageDataRepository.GetMstStage(mstStageId);
            var mstQuest = MstQuestDataRepository.GetMstQuestModel(mstStage.MstQuestId);
            var userStageEnhance = GameRepository.GetGameFetch().UserStageEnhanceModels.FirstOrDefault();
            var enhanceQuestModel = EnhanceQuestModelFactory.CreateCurrentEnhanceQuestModel();

            // プレミアムパス（広告スキップパス）所持判定
            var heldAdSkipPassInfo = HeldAdSkipPassInfoModelFactory.CreateHeldAdSkipPassInfo();
            var hasAdSkipPass = !heldAdSkipPassInfo.IsEmpty();

            // コイン獲得クエスト(EnhanceQuest)の場合は挑戦回数を考慮
            if (!enhanceQuestModel.IsEmpty() && enhanceQuestModel.MstStage.Id == mstStageId)
            {
                // 通常挑戦回数
                var challengeLimit = MstConfigRepository.GetConfig(MstConfigKey.EnhanceQuestChallengeLimit).Value.ToInt();
                var campaignModel = CampaignModelFactory.CreateCampaignModel(
                    enhanceQuestModel.MstQuest.Id,
                    CampaignTargetType.EnhanceQuest,
                    CampaignTargetIdType.Quest,
                    enhanceQuestModel.MstQuest.Difficulty,
                    CampaignType.ChallengeCount);
                if (!campaignModel.IsEmpty())
                {
                    challengeLimit += campaignModel.EffectValue.Value;
                }
                var usedChallengeCount = userStageEnhance?.ResetChallengeCount.Value ?? 0;
                var remainingChallengeCount = challengeLimit - usedChallengeCount;

                // 広告挑戦回数
                var adLimit = MstConfigRepository.GetConfig(MstConfigKey.EnhanceQuestChallengeAdLimit).Value.ToInt();
                var usedAdChallengeCount = userStageEnhance?.ResetAdChallengeCount.Value ?? 0;
                var remainingAdChallengeCount = adLimit - usedAdChallengeCount;

                // パス所持時は通常挑戦回数または広告挑戦回数が残っていればリトライ可能
                if (hasAdSkipPass)
                {
                    if (remainingChallengeCount > 0)
                    {
                        return new InGameRetryModel(RetryAvailableFlag.True, AdChallengeFlag.False);
                    }
                    
                    if (remainingAdChallengeCount > 0)
                    {
                        return new InGameRetryModel(RetryAvailableFlag.True, AdChallengeFlag.True);
                    }
                    
                    return InGameRetryModel.Empty;
                }

                // パス未所持時は通常挑戦回数のみで判定
                return remainingChallengeCount > 0
                    ? new InGameRetryModel(RetryAvailableFlag.True, AdChallengeFlag.False)
                    : InGameRetryModel.Empty;
            }

            // いいじゃん祭(EventQuest)の場合は挑戦回数を考慮
            if (mstQuest.QuestType == QuestType.Event)
            {
                var mstStageEventSetting = MstStageEventSettingDataRepository.GetStageEventSettingFirstOrDefault(mstStageId);
                // 挑戦回数上限が設定されている場合のみチェック
                if (!mstStageEventSetting.IsEmpty() && !mstStageEventSetting.ClearableCount.IsEmpty())
                {
                    var challengeLimit = mstStageEventSetting.ClearableCount;

                    // キャンペーン中は挑戦回数増やす
                    var campaignModel = CampaignModelFactory.CreateCampaignModel(
                        mstQuest.Id,
                        CampaignTargetType.EventQuest,
                        CampaignTargetIdType.Quest,
                        mstQuest.Difficulty,
                        CampaignType.ChallengeCount);
                    if (!campaignModel.IsEmpty())
                    {
                        challengeLimit += campaignModel.EffectValue;
                    }

                    // ユーザーの挑戦回数を確認(広告視聴分は含まない)
                    var userStageEvent = GameRepository.GetGameFetch()
                        .UserStageEventModels.FirstOrDefault(u => u.MstStageId == mstStageId, UserStageEventModel.Empty);

                    if (!userStageEvent.IsEmpty())
                    {
                        // ResetTypeがDailyの場合は日跨ぎリセットカウント、それ以外は全期間の合計カウントを使用
                        var currentClearCount = mstStageEventSetting.ResetType == ResetType.Daily
                            ? userStageEvent.ResetClearCount
                            : userStageEvent.TotalClearCount;

                        // 挑戦回数が上限に達しているか
                        if (currentClearCount >= challengeLimit)
                        {
                            return InGameRetryModel.Empty;
                        }
                    }
                }
            }

            // 通常ステージは上限回数がないか、上限に到達していなければリトライ可能
            return new InGameRetryModel(RetryAvailableFlag.True, AdChallengeFlag.False);
        }

        InGameRetryModel AdventBattleRetryAvailable()
        {
            var mstAdventBattleModels = MstAdventBattleDataRepository.GetMstAdventBattleModels();
            var mstAdventBattleModel = mstAdventBattleModels.FirstOrDefault(
                model =>
                    CalculateTimeCalculator.IsValidTime(
                        TimeProvider.Now,
                        model.StartDateTime.Value,
                        model.EndDateTime.Value),
                MstAdventBattleModel.Empty);
            if (mstAdventBattleModel.IsEmpty())
            {
                return InGameRetryModel.Empty;
            }

            var userAdventBattleModel = GameRepository.GetGameFetch()
                .UserAdventBattleModels.FirstOrDefault(
                    model => model.MstAdventBattleId == mstAdventBattleModel.Id,
                    UserAdventBattleModel.Empty);

            var challengeableCount = mstAdventBattleModel.ChallengeCount;
            var campaignModel = CampaignModelFactory.CreateCampaignModel(
                MasterDataId.Empty,
                CampaignTargetType.AdventBattle,
                CampaignTargetIdType.Quest,
                Difficulty.Normal,
                CampaignType.ChallengeCount);
            if (!campaignModel.IsEmpty())
            {
                challengeableCount += campaignModel.EffectValue;
            }
            challengeableCount -= userAdventBattleModel.ResetChallengeCount;

            var adChallengeLimit = mstAdventBattleModel.AdChallengeCount ?? AdventBattleChallengeCount.Empty;
            var usedAdChallengeCount = userAdventBattleModel.ResetAdChallengeCount ?? AdventBattleChallengeCount.Empty;
            var adChallengeableCount = adChallengeLimit - usedAdChallengeCount;

            var heldAdSkipPassInfo = HeldAdSkipPassInfoModelFactory.CreateHeldAdSkipPassInfo();
            var hasAdSkipPass = !heldAdSkipPassInfo.IsEmpty();

            // パス所持時は通常挑戦回数または広告挑戦回数が残っていればリトライ可能
            if (hasAdSkipPass)
            {
                if (challengeableCount.Value > 0)
                {
                    return new InGameRetryModel(RetryAvailableFlag.True, AdChallengeFlag.False);
                }
                
                if (adChallengeableCount.Value > 0)
                {
                    return new InGameRetryModel(RetryAvailableFlag.True, AdChallengeFlag.True);
                }
                
                return InGameRetryModel.Empty;
            }

            // パス未所持時は通常挑戦回数のみで判定
            return challengeableCount.Value > 0
                ? new InGameRetryModel(RetryAvailableFlag.True, AdChallengeFlag.False)
                : InGameRetryModel.Empty;
        }

        InGameRetryModel PvpRetryAvailable()
        {
            // ランクマッチはリトライ不可
            return InGameRetryModel.Empty;
        }
    }
}