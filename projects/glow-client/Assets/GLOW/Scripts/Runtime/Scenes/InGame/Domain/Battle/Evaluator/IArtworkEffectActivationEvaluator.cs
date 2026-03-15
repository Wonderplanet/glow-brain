using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.ArtworkEffect;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.Evaluator
{
    public interface IArtworkEffectActivationEvaluator
    {
        ArtworkEffectActivationFlag EvaluateActivation(
            IReadOnlyList<ArtworkEffectActivationRuleModel> activationRuleModels,
            IReadOnlyList<DeckUnitModel> unitModels);

        ArtworkEffectActivationFlag EvaluateActivation(
            IReadOnlyList<ArtworkEffectActivationRuleModel> activationRuleModels,
            IReadOnlyList<MstCharacterModel> unitModels);
    }
}
