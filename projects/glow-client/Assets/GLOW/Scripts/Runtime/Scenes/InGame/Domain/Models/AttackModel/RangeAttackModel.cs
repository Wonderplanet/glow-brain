using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.AttackModel
{
    public record RangeAttackModel(
        AttackId Id,
        FieldObjectId AttackerId,
        StateEffectSourceId AttackerStateEffectSourceId,
        AttackElement AttackElement,
        CharacterUnitRoleType AttackerRoleType,
        CharacterColor AttackerColor,
        IReadOnlyList<CharacterColor> KillerColors,
        KillerPercentage KillerPercentage,
        AttackPower BasePower,
        HealPower HealPower,
        CharacterColorAdvantageAttackBonus ColorAdvantageAttackBonus,
        IReadOnlyList<PercentageM> BuffPercentages,
        IReadOnlyList<PercentageM> DebuffPercentages,
        TickCount RemainingDelay,
        AttackTargetSelectionData TargetSelectionData,
        bool IsEnd) : IAttackModel
    {
        public AttackViewId ViewId => AttackElement.AttackViewId;

        public bool IsEmpty()
        {
            return false;
        }

        public (IAttackModel, IReadOnlyList<IAttackResultModel>) UpdateAttackModel(AttackModelContext context)
        {
            RangeAttackModel updatedAttack;
            IReadOnlyList<IAttackResultModel> attackResults = Array.Empty<IAttackResultModel>();

            if (!RemainingDelay.IsEmpty())
            {
                var remainingDelay = RemainingDelay - context.TickCount;

                if (remainingDelay.IsZero())
                {
                    updatedAttack = this with { RemainingDelay = remainingDelay, IsEnd = true };
                    attackResults = GetAttackResults(context);
                }
                else
                {
                    updatedAttack = this with { RemainingDelay = remainingDelay, IsEnd = false };
                }
            }
            else
            {
                updatedAttack = this with { IsEnd = true };
                attackResults = GetAttackResults(context);
            }

            return (updatedAttack, attackResults);
        }

        IReadOnlyList<IAttackResultModel> GetAttackResults(AttackModelContext context)
        {
            IReadOnlyList<IAttackTargetModel> targets = AttackTargetSelector.GetTargetsInRange(
                context.AttackTargetCandidates,
                TargetSelectionData,
                context.CoordinateConverter);

            if (targets.Count == 0)
            {
                return Array.Empty<IAttackResultModel>();
            }

            var attackResults = targets
                .Where(_ => context.RandomProvider.Trial(AttackElement.Probability))
                .Select(target => context.AttackResultModelFactory.CreateHitAttackResult(this, AttackElement, target.Id))
                .ToList();

            foreach (var subElement in AttackElement.SubElements)
            {
                var attackResultsBySubElement = AttackResultDeterminer.DetermineAttackResultsByAttackSubElement(
                    subElement,
                    this,
                    TargetSelectionData.AttackerBattleSide,
                    TargetSelectionData.AttackTarget,
                    targets,
                    context.RandomProvider,
                    context.CoordinateConverter,
                    context.AttackResultModelFactory);

                attackResults.AddRange(attackResultsBySubElement);
            }

            return attackResults;
        }
    }
}
