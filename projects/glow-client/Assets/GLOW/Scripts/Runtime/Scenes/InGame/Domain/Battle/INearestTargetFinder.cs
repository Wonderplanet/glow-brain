using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface INearestTargetFinder
    {
        float GetNearestFoeOrTargetPos(
            CharacterUnitModel myUnit,
            AttackTargetSelectionData attackTargetSelectionData,
            IReadOnlyList<IAttackTargetModel> sortedPlayerAttackTargetCandidates,
            IReadOnlyList<IAttackTargetModel> sortedEnemyAttackTargetCandidates,
            ICoordinateConverter coordinateConverter);
    }
}