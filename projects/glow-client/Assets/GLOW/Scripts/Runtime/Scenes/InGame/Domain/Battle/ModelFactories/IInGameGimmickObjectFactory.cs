using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IInGameGimmickObjectFactory
    {
        InGameGimmickObjectModel Generate(
            InGameGimmickObjectGenerationModel gimmickObjectGenerationModel,
            InGameGimmickObjectAssetKey assetKey,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page);
    }
}
