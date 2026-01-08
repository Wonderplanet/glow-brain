using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public interface IInGameGimmickObjectInitializer
    {
        IReadOnlyList<InGameGimmickObjectModel> Initialize(
            MstAutoPlayerSequenceModel mstAutoPlayerSequenceModel,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel mstPage);
    }
}
