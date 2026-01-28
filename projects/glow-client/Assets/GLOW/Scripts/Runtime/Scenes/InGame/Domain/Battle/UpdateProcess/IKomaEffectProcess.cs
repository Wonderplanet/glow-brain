using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IKomaEffectProcess
    {
        KomaEffectProcessResult UpdateKomaEffects(
            IReadOnlyList<CharacterUnitModel> characterUnits,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel mstPageModel,
            TickCount tickCount,
            bool isBossAppearancePause);
    }
}
