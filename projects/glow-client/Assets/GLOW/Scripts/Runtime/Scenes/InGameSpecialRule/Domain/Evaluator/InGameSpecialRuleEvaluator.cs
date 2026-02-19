using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGameSpecialRule.Domain.Evaluator
{
    public class InGameSpecialRuleEvaluator : IInGameSpecialRuleEvaluator
    {
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IMstEnemyOutpostDataRepository MstEnemyOutpostDataRepository { get; }
        [Inject] IMstStageEndConditionDataRepository MstStageEndConditionDataRepository { get; }

        public ExistsSpecialRuleFlag ExistsSpecialRule(InGameContentType contentType, MasterDataId targetMstId, QuestType questType)
        {
            if (targetMstId == MasterDataId.Empty) return ExistsSpecialRuleFlag.False;

            // マスターデータのMstInGameSpecialRule
            var mstInGameSpecialRules = MstInGameSpecialRuleDataRepository
                .GetInGameSpecialRuleModels(targetMstId, contentType);
            // メインクエストのスピードアタックは特別ルール扱いしない
            // Pvp以外の場合、UnitStatusも特別ルール扱いしない
            var existsMstInGameSpecialRule = mstInGameSpecialRules.Any(
                mst => !(mst.RuleType == RuleType.SpeedAttack && questType == QuestType.Normal) &&
                       !(mst.RuleType == RuleType.UnitStatus && contentType != InGameContentType.Pvp));

            if (existsMstInGameSpecialRule)
            {
                return ExistsSpecialRuleFlag.True;
            }

            // オブジェクト防衛
            IMstInGameModel mstInGameModel = contentType == InGameContentType.Stage
                ? MstStageDataRepository.GetMstStage(targetMstId)
                : MstAdventBattleDataRepository.GetMstAdventBattleModel(targetMstId);

            if (!mstInGameModel.MstDefenseTargetId.IsEmpty())
            {
                return ExistsSpecialRuleFlag.True;
            }

            // ステージの特別ルール
            if (contentType == InGameContentType.Stage)
            {
                // 敵ゲート破壊不可
                var mstEnemyOutpostModel = MstEnemyOutpostDataRepository.GetEnemyOutpost(mstInGameModel.MstEnemyOutpostId);
                if (mstEnemyOutpostModel.IsDamageInvalidation)
                {
                    return ExistsSpecialRuleFlag.True;
                }

                // 合計n体の敵を撃破すると勝利
                var mstStageEndConditions = MstStageEndConditionDataRepository.GetMstStageEndConditions(targetMstId);

                if (ExistsStageEndCondition(mstStageEndConditions, StageEndConditionType.DefeatedEnemyCount))
                {
                    return ExistsSpecialRuleFlag.True;
                }

                // 特定IDの敵を撃破すると勝利
                if (ExistsStageEndCondition(mstStageEndConditions, StageEndConditionType.DefeatUnit))
                {
                    return ExistsSpecialRuleFlag.True;
                }
            }

            return ExistsSpecialRuleFlag.False;
        }

        bool ExistsStageEndCondition(
            IReadOnlyList<MstStageEndConditionModel> mstStageEndConditions,
            StageEndConditionType conditionType)
        {
            var mstStageEndCondition = mstStageEndConditions.FirstOrDefault(
                model => model.ConditionType == conditionType);

            return mstStageEndCondition != null;
        }
    }
}
