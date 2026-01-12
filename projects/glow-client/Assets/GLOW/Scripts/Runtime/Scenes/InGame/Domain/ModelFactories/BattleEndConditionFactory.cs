using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using JetBrains.Annotations;

namespace GLOW.Scenes.InGame.Domain.ModelFactories
{
    public class BattleEndConditionFactory : IBattleEndConditionFactory
    {
        public List<BattleEndCondition> CreateBattleEndConditionsForStage(
            MstQuestModel mstQuestModel,
            IMstInGameModel mstInGameModel,
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            if (mstQuestModel.QuestType == QuestType.Enhance)
            {
                return CreateBattleEndConditionsForEnhanceStage();
            }

            if (mstInGameSpecialRuleModels.Any(mst => mst.RuleType == RuleType.SpeedAttack))
            {
                return CreateBattleEndConditionsForSpeedAttackStage(mstInGameModel);
            }

            return CreateBattleEndConditionsForBasicStage(mstInGameModel, mstInGameSpecialRuleModels);
        }

        public List<BattleEndCondition> CreateBattleEndConditionsForAdventBattle()
        {
            var conditions = new List<BattleEndCondition>();

            // プレイヤーゲート破壊でFinish
            conditions.Add(CreatePlayerOutpostBreakDownBattleEndCondition(StageEndType.Finish));

            // 時間切れでFinish
            conditions.Add(CreateTimeOverBattleEndCondition(StageEndType.Finish));

            return conditions;
        }

        List<BattleEndCondition> CreateBattleEndConditionsForBasicStage(IMstInGameModel mstInGameModel, IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            var conditions = new List<BattleEndCondition>();

            // 敵ゲート破壊で勝利
            conditions.Add(CreateEnemyOutpostBreakDownBattleEndCondition(StageEndType.Victory));

            // プレイヤーゲート破壊で敗北
            conditions.Add(CreatePlayerOutpostBreakDownBattleEndCondition(StageEndType.Defeat));

            // 防衛オブジェクト破壊で敗北
            if (!mstInGameModel.MstDefenseTargetId.IsEmpty())
            {
                conditions.Add(CreateDefenseTargetBreakDownBattleEndCondition(StageEndType.Defeat));
            }

            return conditions;
        }

        List<BattleEndCondition> CreateBattleEndConditionsForSpeedAttackStage(IMstInGameModel mstInGameModel)
        {
            var conditions = new List<BattleEndCondition>();

            // 敵ゲート破壊で勝利
            conditions.Add(CreateEnemyOutpostBreakDownBattleEndCondition(StageEndType.Victory));

            // プレイヤーゲート破壊で敗北
            conditions.Add(CreatePlayerOutpostBreakDownBattleEndCondition(StageEndType.Defeat));

            // 防衛オブジェクト破壊で敗北
            if (!mstInGameModel.MstDefenseTargetId.IsEmpty())
            {
                conditions.Add(CreateDefenseTargetBreakDownBattleEndCondition(StageEndType.Defeat));
            }

            return conditions;
        }

        List<BattleEndCondition> CreateBattleEndConditionsForEnhanceStage()
        {
            var conditions = new List<BattleEndCondition>();

            // プレイヤーゲート破壊でFinish
            conditions.Add(CreatePlayerOutpostBreakDownBattleEndCondition(StageEndType.Finish));

            // 時間切れでFinish
            conditions.Add(CreateTimeOverBattleEndCondition(StageEndType.Finish));

            return conditions;
        }

        BattleEndCondition CreateEnemyOutpostBreakDownBattleEndCondition(StageEndType endType)
        {
            return new BattleEndCondition(
                endType,
                StageEndConditionType.EnemyOutpostBreakDown,
                BattleEndConditionValue.Empty,
                BattleEndConditionValue.Empty);
        }

        BattleEndCondition CreatePlayerOutpostBreakDownBattleEndCondition(StageEndType endType)
        {
            return new BattleEndCondition(
                endType,
                StageEndConditionType.PlayerOutpostBreakDown,
                BattleEndConditionValue.Empty,
                BattleEndConditionValue.Empty);
        }

        BattleEndCondition CreateTimeOverBattleEndCondition(StageEndType endType)
        {
            return new BattleEndCondition(
                endType,
                StageEndConditionType.TimeOver,
                BattleEndConditionValue.Empty,
                BattleEndConditionValue.Empty);
        }

        BattleEndCondition CreateDefenseTargetBreakDownBattleEndCondition(StageEndType endType)
        {
            return new BattleEndCondition(
                endType,
                StageEndConditionType.DefenseTargetBreakDown,
                BattleEndConditionValue.Empty,
                BattleEndConditionValue.Empty);
        }
    }
}
