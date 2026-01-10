using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class NearestTargetFinder : INearestTargetFinder
    {
        public float GetNearestFoeOrTargetPos(
            CharacterUnitModel myUnit,
            AttackTargetSelectionData attackTargetSelectionData,
            IReadOnlyList<IAttackTargetModel> sortedPlayerAttackTargetCandidates,
            IReadOnlyList<IAttackTargetModel> sortedEnemyAttackTargetCandidates,
            ICoordinateConverter coordinateConverter)
        {
            var sortedFoeTargets = myUnit.BattleSide == BattleSide.Player 
                ? sortedEnemyAttackTargetCandidates 
                : sortedPlayerAttackTargetCandidates;
            var sortedFriendTargets = myUnit.BattleSide == BattleSide.Player 
                ? sortedPlayerAttackTargetCandidates 
                : sortedEnemyAttackTargetCandidates;

            float nearestUnitPos = float.MaxValue;

            foreach (var target in sortedFoeTargets)
            {
                var targetPos = coordinateConverter.ToFoeOutpostCoord(target.BattleSide, target.Pos);

                if (targetPos.X >= myUnit.Pos.X)
                {
                    nearestUnitPos = targetPos.X;
                    break;
                }
            }

            if (AttackTargetSelector.IsFriendTarget(attackTargetSelectionData))
            {
                for (var i = sortedFriendTargets.Count - 1; i >= 0; i--)
                {
                    var target = sortedFriendTargets[i];
                    
                    if (target.Id == myUnit.Id)
                    {
                        continue;
                    }

                    if (target.Pos.X < myUnit.Pos.X)
                    {
                        continue;
                    }

                    if (target.Pos.X >= nearestUnitPos)
                    {
                        break;
                    }
                    
                    if (target is not CharacterUnitModel)
                    {
                        continue;
                    }
                    
                    var unitTarget = (CharacterUnitModel)target;
                    if (!AttackTargetSelector.IsTarget(unitTarget, attackTargetSelectionData))
                    {
                        continue;
                    }
                    
                    nearestUnitPos = target.Pos.X;
                    break;
                }
            }

            return nearestUnitPos;
        }
    }
}