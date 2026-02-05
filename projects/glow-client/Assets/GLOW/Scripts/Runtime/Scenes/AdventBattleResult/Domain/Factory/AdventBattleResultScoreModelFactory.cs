using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.AdventBattleResult.Domain.Model;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.AdventBattleResult.Domain.Factory
{
    public class AdventBattleResultScoreModelFactory : IAdventBattleResultScoreModelFactory
    {
        [Inject] ISelectedStageEvaluator SelectedStageEvaluator { get; }

        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }

        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        public AdventBattleResultScoreModel CreateAdventBattleScoreModel(
            AdventBattleScore beforeTotalScore,
            AdventBattleScore afterTotalScore,
            InGameScoreModel inGameScoreModel,
            IReadOnlyList<AdventBattleRewardModel> rankRewards)
        {
            SelectedStageModel selectedStage = SelectedStageEvaluator.GetSelectedStage();

            var mstAdventBattleRankModels = MstAdventBattleDataRepository
                .GetMstAdventBattleScoreRanks(selectedStage.SelectedMstAdventBattleId)
                .OrderBy(model => model.RequiredLowerScore)
                .ToList();

            var beforeRankModel = MstAdventBattleScoreRankModel.Empty;
            var beforeRankModelIndex = -1;
            for (var i = 0; i < mstAdventBattleRankModels.Count; i++)
            {
                var model = mstAdventBattleRankModels[i];
                if (model.RequiredLowerScore <= beforeTotalScore)
                {
                    // 加算前のランクモデルを取得
                    beforeRankModel = model;
                    beforeRankModelIndex = i;
                }
                else
                {
                    // 加算前のランクの次のランクの情報までを取得
                    break;
                }
            }

            var updatedRankModelIndex = 0;
            for (var i = 0; i < mstAdventBattleRankModels.Count; i++)
            {
                var model = mstAdventBattleRankModels[i];
                if (model.RequiredLowerScore > afterTotalScore)
                {
                    // 加算後のランクの次のランクの情報までを取得
                    updatedRankModelIndex = i;
                    break;
                }

                if (i == mstAdventBattleRankModels.Count - 1)
                {
                    // 最後のランクまで到達した場合は、全てのランクを取得
                    updatedRankModelIndex = mstAdventBattleRankModels.Count - 1;
                }
            }

            // ランクモデルの差分を取得
            var betweenRankModels = mstAdventBattleRankModels
                .Skip(beforeRankModelIndex + 1)
                .Take(updatedRankModelIndex - beforeRankModelIndex)
                .ToList();

            var adventBattleResultScoreRankTargetModels = new List<AdventBattleResultScoreRankTargetModel>();

            var beforeScore = beforeTotalScore;
            var beforeRequiredLowerScore = beforeRankModel.RequiredLowerScore;
            foreach (var rankModel in betweenRankModels)
            {
                var targetScore = AdventBattleScore.Min(afterTotalScore, rankModel.RequiredLowerScore);
                if (targetScore.IsEmpty())
                {
                    targetScore = afterTotalScore;
                }

                var targetModel = new AdventBattleResultScoreRankTargetModel(
                    beforeScore,
                    targetScore,
                    rankModel.RequiredLowerScore,
                    rankModel.RankType,
                    rankModel.ScoreRankLevel,
                    beforeRequiredLowerScore);

                adventBattleResultScoreRankTargetModels.Add(targetModel);

                beforeScore = targetModel.AfterTotalScore;
                beforeRequiredLowerScore = targetModel.TargetRankLowerRequiredScore;
            }

            return new AdventBattleResultScoreModel(
                beforeRankModel.RankType,
                beforeRankModel.ScoreRankLevel,
                adventBattleResultScoreRankTargetModels,
                CreateCommonReceiveModel(rankRewards),
                inGameScoreModel.GetScoreByType(InGameScoreType.Damage).ToAdventBattleScore(),
                inGameScoreModel.GetScoreByType(InGameScoreType.EnemyDefeat).ToAdventBattleScore(),
                inGameScoreModel.GetScoreByType(InGameScoreType.BossEnemyDefeat).ToAdventBattleScore());
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveModel(IReadOnlyList<AdventBattleRewardModel> rankRewards)
        {
            return rankRewards
                .Select(r =>
                    new CommonReceiveResourceModel(
                        r.RewardModel.UnreceivedRewardReasonType,
                        PlayerResourceModelFactory.Create(
                            r.RewardModel.ResourceType,
                            r.RewardModel.ResourceId,
                            r.RewardModel.Amount),
                        PlayerResourceModelFactory.Create(r.RewardModel.PreConversionResource))
                )
                .ToList();
        }
    }
}
