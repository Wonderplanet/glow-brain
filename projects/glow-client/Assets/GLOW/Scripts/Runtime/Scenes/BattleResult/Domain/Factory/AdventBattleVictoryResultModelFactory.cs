using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.AdventBattle.Domain.Definition.Service;
using GLOW.Scenes.AdventBattle.Domain.Factory;
using GLOW.Scenes.AdventBattleResult.Domain.Factory;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;
using GLOW.Scenes.BattleResult.Domain.Evaluator;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.PvpBattleResult.Domain.Model;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public class AdventBattleVictoryResultModelFactory : IAdventBattleVictoryResultModelFactory
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IMstEventBonusUnitDataRepository MstEventBonusUnitDataRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IAdventBattleInGameEndBattleLogModelFactory AdventBattleInGameEndBattleLogModelFactory { get; }
        [Inject] IAdventBattleResultScoreModelFactory AdventBattleResultScoreModelFactory { get; }
        [Inject] IAdventBattleScoreModelFactory AdventBattleScoreModelFactory { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }
        [Inject] IUserLevelUpEffectModelFactory UserLevelUpEffectModelFactory { get; }
        [Inject] IUserExpGainModelsFactory UserExpGainModelsFactory { get; }
        [Inject] IAcquiredPlayerResourceModelFactory AcquiredPlayerResourceModelFactory { get; }
        [Inject] IAdventBattleService AdventBattleService { get; }
        [Inject] IGameService GameService { get; }
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IRandomProvider RandomProvider { get; }
        [Inject] IInGameRetryEvaluator InGameRetryEvaluator { get; }

        public async UniTask<VictoryResultModel> CreateVictoryAdventBattleResultModel(
            CancellationToken cancellationToken,
            MasterDataId mstAdventBattleId)
        {
            var prevGameFetchModel = GameRepository.GetGameFetch();
            var prevGameFetchOther = GameRepository.GetGameFetchOther();

            var mstAdventBattleModel =
                MstAdventBattleDataRepository.GetMstAdventBattleModelFirstOrDefault(mstAdventBattleId);

            var endBattleLogModel = AdventBattleInGameEndBattleLogModelFactory.CreateInGameEndBattleLogModel(
                prevGameFetchOther.UserUnitModels,
                InGameScene.StageTimeModel.CurrentTickCount.ToStageClearTime()
            );

            var battleResultModel = await AdventBattleService.End(cancellationToken, mstAdventBattleId, endBattleLogModel);
            var fetchResultModel = await GameService.Fetch(cancellationToken);


            var prevUserAdventBattleModel = prevGameFetchModel.UserAdventBattleModels.FirstOrDefault(
                model => model.MstAdventBattleId == mstAdventBattleId, UserAdventBattleModel.Empty);
            var prevAdventBattleTotalScore = prevUserAdventBattleModel.TotalScore;
            var updatedAdventBattleTotalScore = battleResultModel.UserAdventBattleModel.TotalScore;

            var adventBattleResultScoreModel = AdventBattleResultScoreModelFactory.CreateAdventBattleScoreModel(
                    prevAdventBattleTotalScore,
                    updatedAdventBattleTotalScore,
                    InGameScene.ScoreModel,
                    battleResultModel.AdventBattleRankRewardModels);

            var eventBonusPercentage = GetBonusUnitPercentage(mstAdventBattleModel.EventBonusGroupId);

            var scoreResultModel = AdventBattleScoreModelFactory.CreateAdventBattleScoreModel(
                prevUserAdventBattleModel,
                eventBonusPercentage);

            var updatedGameFetchModel = fetchResultModel.FetchModel;

            var gameFetchOther = GameRepository.GetGameFetchOther();

            var updatedRaidTotalScore = battleResultModel.TotalScore;
            var raidTotalScoreModel = new AdventBattleRaidTotalScoreModel(
                mstAdventBattleId,
                updatedRaidTotalScore);

            var updatedGameFetchOther = gameFetchOther with
            {
                UserItemModels = gameFetchOther.UserItemModels.Update(battleResultModel.UserItemModels),
                UserEnemyDiscoverModels = gameFetchOther.UserEnemyDiscoverModels.Update(battleResultModel.UserEnemyDiscoverModels),
                UserConditionPackModels = gameFetchOther.UserConditionPackModels.Update(battleResultModel.UserConditionPackModels),
                AdventBattleRaidTotalScoreModel = raidTotalScoreModel,
            };

            GameManagement.SaveGameUpdateAndFetch(updatedGameFetchModel, updatedGameFetchOther);

            var prevUserParameter = prevGameFetchModel.UserParameterModel;

            var levelUpResultModel = new UserLevelUpResultModel(
                prevUserParameter.Exp,
                updatedGameFetchModel.UserParameterModel.Exp,
                Array.Empty<UsrLevelRewardResultModel>());

            // レベルアップ
            var userLevelUpEffectModel = UserLevelUpEffectModelFactory.Create(
                battleResultModel.UserLevelUpResultModel,
                prevUserParameter.Level,
                updatedGameFetchModel.UserParameterModel.Level
                );

            // 降臨バトルインゲームクリア報酬
            var inGameClearRewards =
                AcquiredPlayerResourceModelFactory.CreateAcquiredPlayerResourcesForAdventBattle(
                    battleResultModel,
                    mstAdventBattleModel);

            var campaignModels = GetAdventBattleResultCampaignModels(mstAdventBattleId);
            CampaignModel targetCampaignModel = CampaignModel.Empty;
            if (campaignModels.Any())
            {
                targetCampaignModel = campaignModels.MaxBy(c => c.RemainingTimeSpan.Value);
            }

            // 所持上限での獲得状態
            var unreceivedRewardReasonTypes = battleResultModel.AdventBattleClearRewardModels
                .Select(r => r.UnreceivedRewardReasonType)
                .Distinct()
                .ToList();

            // 再挑戦できるか
            var inGameRetryModel = InGameRetryEvaluator.DetermineRetryAvailableFlag();

            // 原画のかけらは降臨バトルでは入手できないので空リスト
            return new VictoryResultModel(
                PickupPlayerUnit().AssetKey,
                UserExpGainModelsFactory.CreateUserExpGainModels(
                    levelUpResultModel,
                    prevUserParameter.Level,
                    prevUserParameter.Exp),
                userLevelUpEffectModel,
                inGameClearRewards,
                Array.Empty<IReadOnlyList<PlayerResourceModel>>(),
                unreceivedRewardReasonTypes,
                new List<ArtworkFragmentAcquisitionModel>(),
                scoreResultModel,
                ResultSpeedAttackModel.Empty,
                adventBattleResultScoreModel,
                PvpBattleResultPointModel.Empty,
                InGameType.AdventBattle,
                targetCampaignModel.RemainingTimeSpan,
                inGameRetryModel
            );
        }

        List<CampaignModel> GetAdventBattleResultCampaignModels(MasterDataId campaignTargetId)
        {
            return CampaignModelFactory.CreateCampaignModels(
                    campaignTargetId,
                    CampaignTargetType.AdventBattle,
                    CampaignTargetIdType.Quest,
                    Difficulty.Normal)
                .Where(c => c.CampaignType is
                    CampaignType.Exp or
                    CampaignType.ArtworkFragment or
                    CampaignType.ItemDrop or
                    CampaignType.CoinDrop)
                .ToList();
        }

        EventBonusPercentage GetBonusUnitPercentage(EventBonusGroupId bonusGroupId)
        {
            var userUnits = GameRepository.GetGameFetchOther().UserUnitModels;
            var currentParty = PartyCacheRepository.GetCurrentPartyModel();
            var mstEventBonusUnits = MstEventBonusUnitDataRepository.GetEventBonuses(bonusGroupId);

            return currentParty.GetUnitList()
                .Where(userUnitId => !userUnitId.IsEmpty())
                .Select(userUnitId =>
                    userUnits.FirstOrDefault(unit => unit.UsrUnitId == userUnitId, UserUnitModel.Empty))
                .Select(userUnit =>
                    mstEventBonusUnits.FirstOrDefault(
                        mstBonus => mstBonus.MstUnitId == userUnit.MstUnitId,
                        MstEventBonusUnitModel.Empty))
                .Aggregate(
                    EventBonusPercentage.Zero,
                    (n, next) => n + next.BonusPercentage);
        }

        DeckUnitModel PickupPlayerUnit()
        {
            var index = RandomProvider.Range(InGameScene.DeckUnits.Count(c => !c.IsEmptyUnit()));
            return InGameScene.DeckUnits[index];
        }
    }
}
