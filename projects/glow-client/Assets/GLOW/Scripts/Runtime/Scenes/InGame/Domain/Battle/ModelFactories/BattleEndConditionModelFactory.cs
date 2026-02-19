using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.Models.BattleEndConditions;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class BattleEndConditionModelFactory : IBattleEndConditionModelFactory
    {
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }

        public IBattleEndConditionModel Create(MstStageEndConditionModel mstModel)
        {
            return Create(mstModel.StageEndType, mstModel.ConditionType, mstModel.ConditionValue1, mstModel.ConditionValue2);
        }

        public IBattleEndConditionModel Create(
            StageEndType stageEndType,
            StageEndConditionType conditionType,
            BattleEndConditionValue conditionValue1,
            BattleEndConditionValue conditionValue2)
        {
            return conditionType switch
            {
                StageEndConditionType.PlayerOutpostBreakDown =>
                    new PlayerOutpostBreakDownBattleEndConditionModel(stageEndType),

                StageEndConditionType.EnemyOutpostBreakDown =>
                    new EnemyOutpostBreakDownBattleEndConditionModel(stageEndType),

                StageEndConditionType.TimeOver =>
                    new TimeOverBattleEndConditionModel(stageEndType),

                StageEndConditionType.DefeatedEnemyCount =>
                    new DefeatedEnemyCountBattleEndConditionModel(
                        stageEndType,
                        conditionValue1.ToDefeatEnemyCount()),

                StageEndConditionType.DefeatUnit =>
                    new DefeatUnitBattleEndConditionModel(
                        stageEndType,
                        conditionValue1.ToMasterDataId(),
                        MstEnemyCharacterDataRepository.GetEnemyCharacter(conditionValue1.ToMasterDataId()).Name,
                        conditionValue2.ToDefeatEnemyCount()),

                StageEndConditionType.DefenseTargetBreakDown =>
                    new DefenseTargetBreakDownBattleEndConditionModel(stageEndType),

                StageEndConditionType.GiveUp =>
                    new GiveUpBattleEndConditionModel(stageEndType),

                _ => EmptyBattleEndConditionModel.Instance
            };
        }
    }
}
