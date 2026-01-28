using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.AdventBattle.Domain.Factory;
using GLOW.Scenes.AdventBattleResult.Domain.Model;
using GLOW.Scenes.BattleResult.Domain.Appliers;
using GLOW.Scenes.BattleResult.Domain.Evaluator;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.EnhanceQuestTop.Domain.Factories;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.PvpBattleResult.Domain.Model;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public class StageVictoryResultModelFactory : IStageVictoryResultModelFactory
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IGameService GameService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IStageService StageService { get; }
        [Inject] IInGameEndBattleLogModelFactory InGameEndBattleLogModelFactory { get; }
        [Inject] IAcquiredPlayerResourceModelFactory AcquiredPlayerResourceModelFactory { get; }
        [Inject] IUserExpGainModelsFactory UserExpGainModelsFactory { get; }
        [Inject] IUserLevelUpEffectModelFactory UserLevelUpEffectModelFactory { get; }
        [Inject] IArtworkFragmentAcquisitionModelFactory ArtworkFragmentAcquisitionModelFactory { get; }
        [Inject] IResultSpeedAttackModelFactory ResultSpeedAttackModelFactory { get; }
        [Inject] IResultScoreModelFactory ResultScoreModelFactory { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }
        [Inject] IRandomProvider RandomProvider { get; }
        [Inject] INextReleaseAnimationApplier NextReleaseAnimationApplier { get; }
        [Inject] IInGameRetryEvaluator InGameRetryEvaluator { get; }

        public async UniTask<VictoryResultModel> VictoryInStage(
            CancellationToken cancellationToken,
            MasterDataId mstStageId)
        {
            var clearTime = GetClearTime();
            var prevGameFetchModel = GameRepository.GetGameFetch();
            var prevGameFetchOther = GameRepository.GetGameFetchOther();
            var prevUserParameter = prevGameFetchModel.UserParameterModel;

            var endBattleLogModel = InGameEndBattleLogModelFactory.CreateInGameEndBattleLogModel(
                prevGameFetchOther.UserUnitModels,
                clearTime
            );

            var stageEndResultModel = await StageService.End(
                cancellationToken,
                mstStageId,
                endBattleLogModel);

            //NOTE: 順番依存。SaveGameUpdateAndFetchより前に呼んで、古いユーザーデータで判定する
            NextReleaseAnimationApplier.UpdateReleaseAnimationRepository(
                mstStageId,
                GameRepository.GetGameFetch().StageModels,
                GameRepository.GetGameFetch().UserStageEventModels);

            var fetchResultModel = await GameService.Fetch(cancellationToken);
            var gameFetchOther = GameRepository.GetGameFetchOther();
            SaveGameUpdateAndFetch(
                fetchResultModel.FetchModel,
                gameFetchOther,
                stageEndResultModel);

            // 獲得経験値
            var userExpGains = UserExpGainModelsFactory.CreateUserExpGainModels(
                stageEndResultModel.UserLevelUp,
                prevUserParameter.Level,
                prevUserParameter.Exp);

            // レベルアップ
            var userLevelUpEffectModel = UserLevelUpEffectModelFactory.Create(
                stageEndResultModel.UserLevelUp,
                prevUserParameter.Level,
                fetchResultModel.FetchModel.UserParameterModel.Level);

            var mstStage = MstStageDataRepository.GetMstStage(mstStageId);
            // 報酬
            var acquiredPlayerResources =
                AcquiredPlayerResourceModelFactory.CreateAcquiredPlayerResources(stageEndResultModel, mstStage);
            var acquiredPlayerResourceGroupList =
                AcquiredPlayerResourceModelFactory.CreateAcquiredPlayerResourcesGroupedByStaminaRap(
                    stageEndResultModel,
                    mstStage);

            // 原画のかけら
            var artworkFragmentAcquisitionModels =
                ArtworkFragmentAcquisitionModelFactory.CreateArtworkFragmentAcquisitionModels(
                    stageEndResultModel,
                    gameFetchOther.UserArtworkFragmentModels);

            // 報酬倍率と現在スコアとハイスコアを取得。ハイスコアはSaveGameUpdateAndFetchにて更新されてしまうためprevGameFetchModelを使用する
            var mstQuest = MstQuestDataRepository.GetMstQuestModel(mstStage.MstQuestId);
            var resultScoreModel = ResultScoreModelFactory.CreateResultScoreModel(
                prevGameFetchModel,
                mstQuest,
                stageEndResultModel.OprCampaignIds);

            var resultSpeedAttackModel = ResultSpeedAttackModelFactory.CreateSpeedAttackModel(
                prevGameFetchModel,
                mstQuest,
                mstStage,
                clearTime);

            // キャラ
            var pickupUnit = PickupPlayerUnit();

            var campaignModels = GetNormalResultCampaignModels(mstStageId);
            CampaignModel targetCampaignModel = CampaignModel.Empty;
            if (campaignModels.Any())
            {
                targetCampaignModel = campaignModels.MaxBy(c => c.RemainingTimeSpan.Value);
            }

            // 所持上限での獲得状態
            var unreceivedRewardReasonTypes = stageEndResultModel.Rewards
                .Select(r => r.RewardModel.UnreceivedRewardReasonType)
                .Distinct()
                .ToList();

            var inGameRetryModel = InGameRetryEvaluator.DetermineRetryAvailableFlag();

            return new VictoryResultModel(
                pickupUnit.AssetKey,
                userExpGains,
                userLevelUpEffectModel,
                acquiredPlayerResources,
                acquiredPlayerResourceGroupList,
                unreceivedRewardReasonTypes,
                artworkFragmentAcquisitionModels,
                resultScoreModel,
                resultSpeedAttackModel,
                AdventBattleResultScoreModel.Empty,
                PvpBattleResultPointModel.Empty,
                InGameType.Normal,
                targetCampaignModel.RemainingTimeSpan,
                inGameRetryModel);
        }

        StageClearTime GetClearTime()
        {
            var stageTime = InGameScene.StageTimeModel;
            var clearTime = stageTime.CurrentTickCount.ToStageClearTime();
            if (!stageTime.StageTimeLimit.IsZero() && stageTime.StageTimeLimit.ToTickCount() < stageTime.CurrentTickCount)
            {
                clearTime = stageTime.StageTimeLimit.ToStageClearTime();
            }

            return clearTime;
        }

        void SaveGameUpdateAndFetch(
            GameFetchModel gameFetchModel,
            GameFetchOtherModel gameFetchOther,
            StageEndResultModel stageEndResultModel)
        {
            var userEmblemModels = stageEndResultModel.Rewards
                .Where(r => r.RewardModel.ResourceType == ResourceType.Emblem)
                .Select(r => new UserEmblemModel(r.RewardModel.ResourceId, NewEncyclopediaFlag.True))
                .ToList();

            var newGameFetchOther = gameFetchOther with
            {
                UserConditionPackModels = gameFetchOther.UserConditionPackModels.Update(stageEndResultModel.ConditionPacks),
                UserArtworkModels = gameFetchOther.UserArtworkModels.Update(stageEndResultModel.UserArtworkModels),
                UserArtworkFragmentModels = gameFetchOther.UserArtworkFragmentModels.Update(stageEndResultModel.UserArtworkFragmentModels),
                UserEmblemModel = gameFetchOther.UserEmblemModel.Update(userEmblemModels),
                UserUnitModels = gameFetchOther.UserUnitModels.Update(stageEndResultModel.UserUnitModels),
                UserItemModels = gameFetchOther.UserItemModels.Update(stageEndResultModel.UserItemModels),
                UserEnemyDiscoverModels = gameFetchOther.UserEnemyDiscoverModels.Update(stageEndResultModel.UserEnemyDiscoverModels),
            };

            GameManagement.SaveGameUpdateAndFetch(gameFetchModel, newGameFetchOther);
        }

        DeckUnitModel PickupPlayerUnit()
        {
            var index = RandomProvider.Range(InGameScene.DeckUnits.Count(c => !c.IsEmptyUnit()));
            return InGameScene.DeckUnits[index];
        }

        List<CampaignModel> GetNormalResultCampaignModels(MasterDataId campaignTargetId)
        {
            var stage = MstStageDataRepository.GetMstStage(campaignTargetId);
            var quest = MstQuestDataRepository.GetMstQuestModel(stage.MstQuestId);
            if (quest.IsEmpty())
            {
                return new List<CampaignModel>();
            }

            CampaignTargetType campaignTargetType = quest.QuestType.ToCampaignTargetType();
            return CampaignModelFactory.CreateCampaignModels(
                    quest.Id,
                    campaignTargetType,
                    CampaignTargetIdType.Quest,
                    quest.Difficulty)
                .Where(c => c.CampaignType is
                    CampaignType.Exp or
                    CampaignType.ArtworkFragment or
                    CampaignType.ItemDrop or
                    CampaignType.CoinDrop)
                .ToList();
        }
    }
}
