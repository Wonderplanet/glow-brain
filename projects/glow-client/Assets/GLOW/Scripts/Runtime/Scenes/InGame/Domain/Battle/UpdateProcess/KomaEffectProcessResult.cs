using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record KomaEffectProcessResult(
        IReadOnlyDictionary<KomaId, KomaModel> UpdatedKomaDictionary,
        IReadOnlyList<CharacterUnitModel> AffectedCharacterUnits,
        List<FieldObjectId> BlockedUnits
        );
}
