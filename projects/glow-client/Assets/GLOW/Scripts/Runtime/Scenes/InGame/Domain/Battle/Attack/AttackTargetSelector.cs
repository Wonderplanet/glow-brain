using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public static class AttackTargetSelector
    {
        public static bool IsTargetInAttackRange(
            IReadOnlyList<IAttackTargetModel> sortedPlayerAttackTargetCandidates,
            IReadOnlyList<IAttackTargetModel> sortedEnemyAttackTargetCandidates,
            AttackTargetSelectionData selectionData,
            ICoordinateConverter coordinateConverter)
        {
            if (selectionData.IsEmpty())
            {
                return false;
            }

            // 攻撃対象が自分自身の場合は常にtrue
            if (selectionData.AttackTarget == AttackTarget.Self)
            {
                return true;
            }

            if (selectionData.FieldCoordRange.IsEmpty())
            {
                return false;
            }

            if (IsFoeTarget(selectionData))
            {
                var sortedFoeTargets = selectionData.AttackerBattleSide == BattleSide.Player
                    ? sortedEnemyAttackTargetCandidates
                    : sortedPlayerAttackTargetCandidates;

                var isTargetInRange = sortedFoeTargets.Any(candidate =>
                    IsTargetInRange(candidate, selectionData, coordinateConverter));

                if (isTargetInRange)
                {
                    return true;
                }
            }
            
            if (IsFriendTarget(selectionData))
            {
                var sortedFriendTargets = selectionData.AttackerBattleSide == BattleSide.Player
                    ? sortedPlayerAttackTargetCandidates
                    : sortedEnemyAttackTargetCandidates;

                var isTargetInRange = sortedFriendTargets.Any(candidate =>
                    IsTargetInRange(candidate, selectionData, coordinateConverter));

                if (isTargetInRange)
                {
                    return true;
                }
            }

            return false;
        }

        public static IReadOnlyList<IAttackTargetModel> GetTargetsInRange(
            IReadOnlyList<IAttackTargetModel> candidates,
            AttackTargetSelectionData selectionData,
            ICoordinateConverter coordinateConverter)
        {
            if (selectionData.IsEmpty())
            {
                return new List<IAttackTargetModel>();
            }

            return candidates
                .Where(candidate => IsTargetInRange(candidate, selectionData, coordinateConverter))
                .OrderByDescending(candidate => candidate.Pos.X)
                .ThenBy(candidate => candidate.AttackTargetOrder)
                .ThenBy(candidate => candidate.PosUpdateStageTickCount)
                .Take(selectionData.MaxTargetCount.Value)
                .ToList();
        }

        public static bool IsTarget(
            IAttackTargetModel candidate,
            AttackTargetSelectionData selectionData)
        {
            if (selectionData.IsEmpty())
            {
                return false;
            }

            if (selectionData.AttackTarget == AttackTarget.Self)
            {
                return selectionData.AttackerId == candidate.Id;
            }

            // FriendOnlyの場合、発動者を除外
            if (selectionData.AttackTarget == AttackTarget.FriendOnly && selectionData.AttackerId == candidate.Id)
            {
                return false;
            }

            BattleSide targetBattleSide = GetTargetBattleSide(selectionData.AttackerBattleSide, selectionData.AttackTarget);

            return targetBattleSide == candidate.BattleSide
                   && (!selectionData.IsDamagedOnly || candidate.Hp < candidate.MaxHp)
                   && IsTargetFieldObjectType(selectionData.AttackTargetType, candidate.FieldObjectType)
                   && selectionData.TargetColors.Contains(candidate.Color)
                   && selectionData.TargetRoles.Contains(candidate.RoleType);
        }
        
        public static bool IsFoeTarget(AttackTargetSelectionData selectionData)
        {
            if (selectionData.IsEmpty())
            {
                return false;
            }

            return selectionData.AttackTarget == AttackTarget.Foe;
        }
        
        public static bool IsFriendTarget(AttackTargetSelectionData selectionData)
        {
            if (selectionData.IsEmpty())
            {
                return false;
            }

            return selectionData.AttackTarget == AttackTarget.Friend || selectionData.AttackTarget == AttackTarget.FriendOnly;
        }

        static bool IsTargetInRange(
            IAttackTargetModel candidate,
            AttackTargetSelectionData selectionData,
            ICoordinateConverter coordinateConverter)
        {
            if (selectionData.AttackTarget == AttackTarget.Self)
            {
                return selectionData.AttackerId == candidate.Id;
            }

            return IsTarget(candidate, selectionData)
                   && IsInAttackRange(candidate, selectionData.FieldCoordRange, coordinateConverter);
        }

        static BattleSide GetTargetBattleSide(BattleSide battleSide, AttackTarget target)
        {
            return target == AttackTarget.Friend || target == AttackTarget.FriendOnly
                ? battleSide
                : battleSide == BattleSide.Player ? BattleSide.Enemy : BattleSide.Player;
        }

        static bool IsTargetFieldObjectType(AttackTargetType attackTargetType, FieldObjectType fieldObjectType)
        {
            // AttackTargetTypeがAllの場合は全て対象
            if (attackTargetType == AttackTargetType.All) return true;

            // 攻撃のTypeと攻撃対象のTypeが同じか
            switch (fieldObjectType)
            {
                case FieldObjectType.Character :
                    return attackTargetType == AttackTargetType.Character;
                case FieldObjectType.Outpost :
                case FieldObjectType.DefenseTarget :
                    return attackTargetType == AttackTargetType.OutpostAndDefenseTarget;
                default:
                    return false;
            }
        }

        static bool IsInAttackRange(
            IAttackTargetModel candidate,
            CoordinateRange attackRange,
            ICoordinateConverter coordinateConverter)
        {
            if (attackRange.IsEmpty()) return true;

            float pos = coordinateConverter.OutpostToFieldCoord(candidate.BattleSide, candidate.Pos).X;
            return attackRange.IsInRange(pos);
        }
    }
}
