using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class InitialEnemyCharacterCoefFactory: IInitialCharacterUnitCoefFactory
    {
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }

        public InitialCharacterUnitCoef GenerateInitialUnitCoef(
            MasterDataId mstUnitId,
            IStageEnemyParameterCoef stageEnemyParameterCoef,
            AutoPlayerSequenceCoefficient inGameSequenceHpCoef,
            AutoPlayerSequenceCoefficient inGameSequenceAttackPowerCoef,
            AutoPlayerSequenceCoefficient inGameSequenceUnitMoveSpeedCoef)
        {
            var mstModel = MstEnemyCharacterDataRepository.GetEnemyStageParameter(mstUnitId);
            if (mstModel.IsBoss)
            {

                return new InitialCharacterUnitCoef(
                    stageEnemyParameterCoef.BossEnemyHpCoef.Value, stageEnemyParameterCoef.BossEnemyAttackCoef.Value,
                    stageEnemyParameterCoef.BossEnemySpeedCoef.Value,
                    inGameSequenceHpCoef.Value, inGameSequenceAttackPowerCoef.Value,inGameSequenceUnitMoveSpeedCoef.Value
                );
            }
            else
            {
                return new InitialCharacterUnitCoef(
                    stageEnemyParameterCoef.MobEnemyHpCoef.Value, stageEnemyParameterCoef.MobEnemyAttackCoef.Value,
                    stageEnemyParameterCoef.MobEnemySpeedCoef.Value,
                    inGameSequenceHpCoef.Value, inGameSequenceAttackPowerCoef.Value, inGameSequenceUnitMoveSpeedCoef.Value
                );
            }
        }
    }
}
