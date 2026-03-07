using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.ArtworkEffect;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.Evaluator
{
    public interface IArtworkEffectTargetEvaluator
    {
        Dictionary<MasterDataId, ArtworkEffectTargetFlag> EvaluateTarget(
            IReadOnlyList<ArtworkEffectTargetRuleModel> targetRuleModels,
            IReadOnlyList<MstCharacterModel> unitModels,
            InGameRandomSeed randomSeed);
    }
}
