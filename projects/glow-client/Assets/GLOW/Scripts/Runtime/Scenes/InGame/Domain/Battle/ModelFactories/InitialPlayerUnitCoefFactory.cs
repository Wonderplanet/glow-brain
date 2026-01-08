using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class InitialPlayerUnitCoefFactory : IInitialCharacterUnitCoefFactory
    {
        public InitialCharacterUnitCoef GenerateInitialUnitCoef(
            MasterDataId mstUnitId,
            IStageEnemyParameterCoef stageEnemyParameterCoef,
            AutoPlayerSequenceCoefficient inGameSequenceHpCoef,
            AutoPlayerSequenceCoefficient inGameSequenceAttackPowerCoef,
            AutoPlayerSequenceCoefficient inGameSequenceUnitMoveSpeedCoef)
        {
            return InitialCharacterUnitCoef.Empty;
        }
    }
}
