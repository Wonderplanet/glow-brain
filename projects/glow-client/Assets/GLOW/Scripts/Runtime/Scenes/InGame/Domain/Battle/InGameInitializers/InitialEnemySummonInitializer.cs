using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class InitialEnemySummonInitializer : IInitialEnemySummonInitializer
    {
        [Inject] InitialEnemyCharacterCoefFactory InitialCharacterUnitCoefFactory { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }
        [Inject] ICharacterUnitFactory CharacterUnitFactory { get; }
        [Inject] IUnitGenerationModelFactory UnitGenerationModelFactory { get; }

        InitialEnemySummonInitializerResult IInitialEnemySummonInitializer.InitializeEnemySummon(
            MstAutoPlayerSequenceModel enemySequenceModel,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel mstPage,
            IMstInGameModel mstInGameModel)
        {
            var initialCharacterUnits = new List<CharacterUnitModel>();
            var initialEnemies = enemySequenceModel.EnemySummonElements
                .Where(e => e.ActivationCondition.Type == AutoPlayerSequenceConditionType.InitialSummon)
                .Where(e => e.SummonPosition != FieldCoordV2.Empty);

            foreach (var autoPlayerSequenceElement in initialEnemies)
            {
                var unitGenerationModel = UnitGenerationModelFactory.Create(
                    autoPlayerSequenceElement,
                    BattleSide.Enemy,
                    mstInGameModel,
                    InitialCharacterUnitCoefFactory);

                var initEnemyUnitModel = SummonInitEnemy(
                    autoPlayerSequenceElement.Action.Value.ToMasterDataId(),
                    unitGenerationModel,
                    komaDictionary,
                    mstPage);

                initialCharacterUnits.Add(initEnemyUnitModel);
            }

            return new InitialEnemySummonInitializerResult(
                initialCharacterUnits
            );
        }

        CharacterUnitModel SummonInitEnemy(
            MasterDataId id,
            UnitGenerationModel unitGenerationModel,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page)
        {
            MstEnemyStageParameterModel stageParameter = MstEnemyCharacterDataRepository.GetEnemyStageParameter(id);

            var charaUnit = CharacterUnitFactory.GenerateEnemyCharacterUnit(
                stageParameter,
                unitGenerationModel,
                BattleSide.Enemy,
                komaDictionary,
                page);

            return charaUnit;
        }
    }
}
