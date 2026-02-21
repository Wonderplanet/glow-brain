using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IScoreUpdateProcess
    {
        ScoreUpdateProcessResult UpdateScore(
            InGameScoreModel scoreModel,
            ScoreCalculateModel scoreCalculateModel,
            IReadOnlyList<CharacterUnitModel> units,
            IReadOnlyList<CharacterUnitModel> deadUnits,
            OutpostModel enemyOutpost,
            IReadOnlyList<AppliedAttackResultModel> appliedAttackResults);
    }
}