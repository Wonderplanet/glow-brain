using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface ICharacterUnitFactory
    {
        CharacterUnitModel GenerateUserCharacterUnit(
            MstCharacterModel mstCharacter,
            BattleSide side,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page);

        CharacterUnitModel GenerateOpponentCharacterUnit(
            MstCharacterModel mstCharacter,
            BattleSide side,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page);

        CharacterUnitModel GenerateEnemyCharacterUnit(
            MstEnemyStageParameterModel stageParameter,
            UnitGenerationModel unitGenerationModel,
            BattleSide side,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page);
    }
}
