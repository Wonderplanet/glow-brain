using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;

namespace GLOW.Scenes.InGame.Domain.Models.AttackModel
{
    public static class AttackResultDeterminer
    {
        public static IReadOnlyList<HitAttackResultModel> DetermineAttackResultsByAttackSubElement(
            AttackSubElement attackSubElement,
            IAttackModel attackModel,
            BattleSide attackerBattleSide,
            AttackTarget attackTarget,
            IReadOnlyList<IAttackTargetModel> attackTargetCandidates,
            IRandomProvider randomProvider,
            ICoordinateConverter coordinateConverter,
            IAttackResultModelFactory attackResultModelFactory)
        {
            var attackTargetSelectionData = new AttackTargetSelectionData(
                attackModel.AttackerId,
                attackerBattleSide,
                attackTarget,
                attackSubElement.AttackTargetType,
                attackSubElement.TargetColors,
                attackSubElement.TargetRoles,
                attackSubElement.TargetSeriesIds,
                attackSubElement.TargetCharacterIds,
                attackSubElement.AttackDamageType == AttackDamageType.Heal,
                FieldObjectCount.Infinity,
                CoordinateRange.Empty);

            IReadOnlyList<IAttackTargetModel> targets = AttackTargetSelector.GetTargetsInRange(
                attackTargetCandidates,
                attackTargetSelectionData,
                coordinateConverter);

            if (targets.Count == 0)
            {
                return Array.Empty<HitAttackResultModel>();
            }

            return targets
                .Where(_ => randomProvider.Trial(attackSubElement.Probability))
                .Select(target => attackResultModelFactory.CreateHitAttackResult(attackModel, attackSubElement, target.Id))
                .ToList();
        }
    }
}
