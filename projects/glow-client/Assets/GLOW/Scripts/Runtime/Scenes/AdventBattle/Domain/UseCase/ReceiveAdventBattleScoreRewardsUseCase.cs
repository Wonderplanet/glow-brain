using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Extensions;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.AdventBattle.Domain.Definition.Service;
using GLOW.Scenes.AdventBattle.Domain.Evaluator;
using GLOW.Scenes.AdventBattle.Domain.Model;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Domain.UseCase
{
    public class ReceiveAdventBattleScoreRewardsUseCase
    {
        [Inject] IAdventBattleService AdventBattleService { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IReceivableRaidTotalScoreRewardsEvaluator ReceivableRaidTotalScoreRewardsEvaluator { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IAdventBattlePreferenceRepository AdventBattlePreferenceRepository { get; }

        public async UniTask<ReceivedAdventBattleScoreRewardsModel> ReceiveRewardsForAdventBattleScore(
            CancellationToken cancellationToken,
            MasterDataId mstAdventBattleId)
        {
            // 受け取れる報酬が存在するかを事前に確認
            // ハイスコアの値に変動がない場合は叩かない
            var userAdventBattleModels = GameRepository.GetGameFetch().UserAdventBattleModels;
            var currentUserAdventBattleModel = userAdventBattleModels
                .FirstOrDefault(model => model.MstAdventBattleId == mstAdventBattleId, UserAdventBattleModel.Empty);
            var currentMstAdventBattleModel = MstAdventBattleDataRepository.GetMstAdventBattleModelFirstOrDefault(mstAdventBattleId);

            if (!CheckCallTopApi(currentUserAdventBattleModel, currentMstAdventBattleModel.BattleType))
            {
                SaveAdventBattleRaidPreference(currentMstAdventBattleModel.BattleType);

                // 報酬を受け取れない場合は空のモデルを返す
                return ReceivedAdventBattleScoreRewardsModel.Empty;
            }

            var result = await AdventBattleService.Top(cancellationToken, mstAdventBattleId);

            var gameFetch = GameRepository.GetGameFetch();
            var updatedGameFetch = gameFetch with
            {
                UserParameterModel = result.UserParameter,
                UserAdventBattleModels = UpdateHighScoreAnimationPlayed(
                    gameFetch.UserAdventBattleModels,
                    currentUserAdventBattleModel)
            };

            var gameFetchOther = GameRepository.GetGameFetchOther();
            var updatedGameFetchOther = gameFetchOther with
            {
                UserItemModels = gameFetchOther.UserItemModels.Update(result.UserItems),
                UserEmblemModel = gameFetchOther.UserEmblemModel.Update(result.UserEmblems)
            };

            GameManagement.SaveGameUpdateAndFetch(updatedGameFetch, updatedGameFetchOther);

            var maxScoreRewards = CreateCommonReceiveModel(result.AdventBattleMaxScoreRewards);

            var raidTotalScoreRewards = CreateCommonReceiveModel(result.AdventBattleRaidTotalScoreRewards);

            SaveAdventBattleRaidPreference(currentMstAdventBattleModel.BattleType);

            var raidTotalScore = GetReceivedRewardMaxRequiredRaidTotalScore(currentMstAdventBattleModel.BattleType, mstAdventBattleId);

            return new ReceivedAdventBattleScoreRewardsModel(
                maxScoreRewards,
                raidTotalScoreRewards,
                raidTotalScore);
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveModel(IReadOnlyList<AdventBattleRewardModel> models)
        {
            return models
                .Select(r =>
                    new CommonReceiveResourceModel(
                        r.RewardModel.UnreceivedRewardReasonType,
                        PlayerResourceModelFactory.Create(
                            r.RewardModel.ResourceType,
                            r.RewardModel.ResourceId,
                            r.RewardModel.Amount),
                        PlayerResourceModelFactory.Create(r.RewardModel.PreConversionResource)))
                .ToList();
        }

        IReadOnlyList<UserAdventBattleModel> UpdateHighScoreAnimationPlayed(
            IReadOnlyList<UserAdventBattleModel> userAdventBattleModels,
            UserAdventBattleModel targetAdventBattleModel)
        {
            var updatedAdventBattleModel = targetAdventBattleModel with
            {
                HighScoreLastAnimationPlayed = targetAdventBattleModel.MaxScore
            };

            var updatedUserAdventBattleModels = userAdventBattleModels
                .Replace(targetAdventBattleModel, updatedAdventBattleModel);

            return updatedUserAdventBattleModels;
        }

        bool CheckCallTopApi(
            UserAdventBattleModel currentAdventBattleModel,
            AdventBattleType battleType)
        {
            bool receivableMaxScoreRewards = !(currentAdventBattleModel.IsEmpty() ||
                                               currentAdventBattleModel.HighScoreLastAnimationPlayed == currentAdventBattleModel.MaxScore);

            if (battleType == AdventBattleType.ScoreChallenge)
            {
                return receivableMaxScoreRewards;
            }
            else
            {
                bool receivableRaidTotalScoreRewards = ReceivableRaidTotalScoreRewardsEvaluator.ExistsReceivableRaidTotalScoreRewards();
                return receivableMaxScoreRewards || receivableRaidTotalScoreRewards;
            }
        }

        void SaveAdventBattleRaidPreference(AdventBattleType battleType)
        {
            if (battleType == AdventBattleType.ScoreChallenge) return;

            var gameFetchOther = GameRepository.GetGameFetchOther();
            var currentRaidTotalScoreModel = gameFetchOther.AdventBattleRaidTotalScoreModel;

            // 現在の協力スコアを前回の協力スコアとして保存
            AdventBattlePreferenceRepository.SetEvaluatedRaidTotalScoreModelForRewards(currentRaidTotalScoreModel);
        }

        AdventBattleRaidTotalScore GetReceivedRewardMaxRequiredRaidTotalScore(AdventBattleType battleType, MasterDataId mstAdventBattleId)
        {
            if (battleType == AdventBattleType.ScoreChallenge) return AdventBattleRaidTotalScore.Empty;

            var gameFetchOther = GameRepository.GetGameFetchOther();
            var currentRaidTotalScoreModel = gameFetchOther.AdventBattleRaidTotalScoreModel;
            if (currentRaidTotalScoreModel.MstAdventBattleId != mstAdventBattleId) return AdventBattleRaidTotalScore.Empty;

            var receivedMaxRequiredTotalScoreRewardModel = MstAdventBattleDataRepository
                .GetMstAdventBattleRewardGroups(mstAdventBattleId)
                .Where(model => model.RewardCategory == AdventBattleRewardCategory.RaidTotalScore)
                .MaxByBelowOrEqualUpperLimit(
                    model => model.RewardCondition.ToAdventBattleRaidTotalScore().Value,
                    currentRaidTotalScoreModel.AdventBattleRaidTotalScore.Value) ?? MstAdventBattleRewardGroupModel.Empty;

            if (receivedMaxRequiredTotalScoreRewardModel.RewardCondition.IsEmpty()) return AdventBattleRaidTotalScore.Empty;

            return receivedMaxRequiredTotalScoreRewardModel.RewardCondition.ToAdventBattleRaidTotalScore();
        }
    }
}
