using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IUnitGenerationModelFactory
    {
        UnitGenerationModel Create(
            MstAutoPlayerSequenceElementModel autoPlayerSequenceElementModel,
            BattleSide battleSide,
            IStageEnemyParameterCoef stageEnemyParameterCoef,
            IInitialCharacterUnitCoefFactory initialCharacterUnitCoefFactory);

        UnitGenerationModel CreateTransformationUnitGenerationModel(CharacterUnitModel unitModel);
    }
}
