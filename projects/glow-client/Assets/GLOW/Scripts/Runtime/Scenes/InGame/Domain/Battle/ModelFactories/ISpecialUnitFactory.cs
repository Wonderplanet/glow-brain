using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface ISpecialUnitFactory
    {
        SpecialUnitModel GenerateSpecialUnit(
            MstCharacterModel mstCharacter,
            BattleSide battleSide,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page,
            PageCoordV2 pos);
    }
}
