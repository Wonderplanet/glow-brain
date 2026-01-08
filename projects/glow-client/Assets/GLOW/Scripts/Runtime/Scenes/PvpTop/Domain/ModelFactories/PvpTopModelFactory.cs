using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Extensions;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using GLOW.Scenes.PvpPreviousSeasonResult.Domain.Models;
using GLOW.Scenes.PvpTop.Domain.Resolver;
using GLOW.Scenes.PvpTop.Domain.UseCase;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.PvpTop.Domain.ModelFactories
{
    public class PvpTopModelFactory : IPvpTopModelFactory
    {
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IPvpOpponentRefreshCoolTimeFactory PvpOpponentRefreshCoolTimeFactory { get; }
        [Inject] IPvpTopOpponentModelFactory PvpTopOpponentModelFactory { get; }
        [Inject] IPvpTopRankingStateFactory PvpTopRankingStateFactory { get; }
        [Inject] IPvpTopUserStateFactory PvpTopUserStateFactory { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMstCurrentPvpModelResolver MstCurrentPvpModelResolver { get; }
        [Inject] IInGameSpecialRuleUnitStatusEvaluator InGameSpecialRuleUnitStatusEvaluator { get; }
        [Inject] IPvpReceivedRewardRepository PvpReceivedRewardRepository { get; }

        public PvpTopUseCaseModel Create(PvpTopResultModel pvpTopResultModel)
        {
            // 現在選択されているパーティのパーティ名を取得
            var partyName = PartyCacheRepository.GetCurrentPartyModel().PartyName;

            // マスターデータからPVP情報を取得
            var sysPvpSeasonModel = GameRepository.GetGameFetchOther().SysPvpSeasonModel;

            var mstPvpModel = MstCurrentPvpModelResolver.CreateMstPvpModel(sysPvpSeasonModel.Id);

            if (mstPvpModel.IsEmpty())
            {
                return PvpTopUseCaseModel.Empty;
            }

            var pvpRankingState = PvpTopRankingStateFactory.Create(
                mstPvpModel,
                sysPvpSeasonModel,
                pvpTopResultModel.IsViewableRankingFromCalculating);

            var hasInGameSpecialRuleUnitStatus = InGameSpecialRuleUnitStatusEvaluator
                .HasSpecialRuleUnitStatus(
                    mstPvpModel.Id,
                    InGameContentType.Pvp);

            var pvpPreviousSeasonResultAnimation = CreatePvpPreviousSeasonResultAnimationModel(
                pvpTopResultModel.PvpPreviousSeasonResult);

            var receivedTotalScoreRewards = GetPvpReceivedTotalScoreRewards();

            var nextTotalScoreRewardModel = GetNextTotalScoreRewardModel(sysPvpSeasonModel.Id);

            return new PvpTopUseCaseModel(
                sysPvpSeasonModel.Id,
                pvpRankingState,
                PvpTopUserStateFactory.Create(pvpRankingState, mstPvpModel, pvpTopResultModel.UsrPvpStatus),
                new RemainingTimeSpan(TimeProvider.Now - sysPvpSeasonModel.EndAt),
                PvpTopOpponentModelFactory.Create(pvpTopResultModel.OpponentSelectStatuses, mstPvpModel.Id),
                partyName,
                PvpOpponentRefreshCoolTimeFactory.CalculateRefreshCoolTime(),
                pvpTopResultModel.IsViewableRankingFromCalculating,
                pvpPreviousSeasonResultAnimation,
                sysPvpSeasonModel.EndAt,
                hasInGameSpecialRuleUnitStatus,
                receivedTotalScoreRewards,
                nextTotalScoreRewardModel
            );
        }

        PvpPreviousSeasonResultAnimationModel CreatePvpPreviousSeasonResultAnimationModel(
            PvpPreviousSeasonResultModel pvpPreviousSeasonResult)
        {
            if (pvpPreviousSeasonResult.IsEmpty()) return PvpPreviousSeasonResultAnimationModel.Empty;

            return pvpPreviousSeasonResult.IsEmpty()
                ? PvpPreviousSeasonResultAnimationModel.Empty
                : new PvpPreviousSeasonResultAnimationModel(
                    pvpPreviousSeasonResult.PvpRankClassType,
                    pvpPreviousSeasonResult.RankClassLevel,
                    pvpPreviousSeasonResult.Score,
                    pvpPreviousSeasonResult.Ranking,
                    pvpPreviousSeasonResult.PvpRewards
                        .Select(reward =>
                            PlayerResourceModelFactory.Create(
                                reward.Reward.ResourceType,
                                reward.Reward.ResourceId,
                                reward.Reward.Amount
                                ))
                        .ToList()
                    );
        }

        PvpReceivedTotalScoreRewardsModel GetPvpReceivedTotalScoreRewards()
        {
            var receivedPvpRewardModels = PvpReceivedRewardRepository.GetReceivedPvpRewardModels();
            // 報酬受け取り後にリポジトリをクリア
            PvpReceivedRewardRepository.Clear();

            if (receivedPvpRewardModels.IsEmpty())
            {
                return PvpReceivedTotalScoreRewardsModel.Empty;
            }

            var totalScoreRewards = receivedPvpRewardModels
                .Where(reward => reward.RewardCategory == PvpRewardCategory.TotalScore)
                .Select(reward => new CommonReceiveResourceModel(
                    reward.Reward.UnreceivedRewardReasonType,
                    PlayerResourceModelFactory.Create(
                        reward.Reward.ResourceType,
                        reward.Reward.ResourceId,
                        reward.Reward.Amount),
                    PlayerResourceModel.Empty))
                .ToList();

            var sysPvpSeasonModel = GameRepository.GetGameFetchOther().SysPvpSeasonModel;

            // 報酬受け取りに表示する獲得済み報酬のポイント
            var receivedTotalScore = GetMaxAchievedTotalScoreRewardPoint(sysPvpSeasonModel.Id);

            return new PvpReceivedTotalScoreRewardsModel(totalScoreRewards, receivedTotalScore);
        }

        PvpTopNextTotalScoreRewardModel GetNextTotalScoreRewardModel(ContentSeasonSystemId id)
        {
            var mstPvpRewardGroupModels = MstCurrentPvpModelResolver.CreateMstPvpRewardGroups(id);
            var receivedTotalScore = GameRepository.GetGameFetchOther().UserPvpStatusModel.MaxReceivedTotalScore;

            var nextReward = mstPvpRewardGroupModels
                .Where(group => group.RewardCategory == PvpRewardCategory.TotalScore)
                .MinByAboveLowerLimit(
                    group => group.ConditionValue.ToPvpPoint().Value,
                    receivedTotalScore.Value) ?? MstPvpRewardGroupModel.Empty;

            if (nextReward.IsEmpty())
            {
                return PvpTopNextTotalScoreRewardModel.Empty;
            }

            var nextTotalScore = nextReward.ConditionValue.ToPvpPoint();
            var firstReward = nextReward.Rewards.FirstOrDefault();

            if (firstReward == null || firstReward.IsEmpty())
            {
                return PvpTopNextTotalScoreRewardModel.Empty;
            }

            var resourceModel = PlayerResourceModelFactory.Create(
                firstReward.ResourceType,
                firstReward.ResourceId,
                firstReward.Amount.ToPlayerResourceAmount());

            return new PvpTopNextTotalScoreRewardModel(resourceModel, nextTotalScore);
        }

        PvpPoint GetMaxAchievedTotalScoreRewardPoint(ContentSeasonSystemId id)
        {
            var userPvpStatusModel = GameRepository.GetGameFetchOther().UserPvpStatusModel;
            var mstPvpRewardGroupModels = MstCurrentPvpModelResolver.CreateMstPvpRewardGroups(id);

            var maxReceivedTotalScore = userPvpStatusModel.MaxReceivedTotalScore;

            // MaxReceivedTotalScore以下の累計スコア報酬の中で最大のものを取得
            var maxAchievedReward = mstPvpRewardGroupModels
                .Where(group => group.RewardCategory == PvpRewardCategory.TotalScore)
                .MaxByBelowOrEqualUpperLimit(
                    group => group.ConditionValue.ToPvpPoint().Value,
                    maxReceivedTotalScore.Value) ?? MstPvpRewardGroupModel.Empty;

            return maxAchievedReward.IsEmpty()
                ? PvpPoint.Empty
                : maxAchievedReward.ConditionValue.ToPvpPoint();
        }
    }
}
