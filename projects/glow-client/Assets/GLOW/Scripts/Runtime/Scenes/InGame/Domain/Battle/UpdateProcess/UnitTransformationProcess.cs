using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class UnitTransformationProcess : IUnitTransformationProcess
    {
        [Inject] IUnitGenerationModelFactory UnitGenerationModelFactory { get; }

        public UnitSummonQueueModel Update(IReadOnlyList<CharacterUnitModel> units, UnitSummonQueueModel unitSummonQueue)
        {
            var updatedUnitSummonQueue = unitSummonQueue;

            var transformationFinishUnits = units
                .Where(unit => unit.Transformation.IsTransformationFinish)
                .ToList();

            if (transformationFinishUnits.Count == 0)
            {
                return updatedUnitSummonQueue;
            }

            var summonQueueElements = transformationFinishUnits
                .Where(unit => unit.BattleSide == BattleSide.Enemy)
                .Select(CreateUnitSummonQueueElement);

            updatedUnitSummonQueue = updatedUnitSummonQueue with
            {
                SummonQueue = updatedUnitSummonQueue.SummonQueue.Concat(summonQueueElements).ToList()
            };

            return updatedUnitSummonQueue;
        }

        UnitSummonQueueElement CreateUnitSummonQueueElement(CharacterUnitModel unit)
        {
            var unitGenerationModel = UnitGenerationModelFactory.CreateTransformationUnitGenerationModel(unit);

            return new UnitSummonQueueElement(
                unit.Transformation.MstEnemyStageParameterId,
                unitGenerationModel);
        }
    }
}
