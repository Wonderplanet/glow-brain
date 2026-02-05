using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.AttackModel
{
    public record DeckAttackModel(
        AttackId Id,
        FieldObjectId AttackerId,
        MasterDataId AttackerCharacterId,
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
            DeckAttackModel updatedAttack;
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
            var attackResultModelFactory = context.AttackResultModelFactory;
            var randomProvider = context.RandomProvider;
            var attackResults = new List<IAttackResultModel>();

            // RushAttackPowerUpの場合は既存処理維持
            if (AttackElement.StateEffect.Type == StateEffectType.RushAttackPowerUp)
            {
                return GetRushAttackPowerUpResults(context);
            }

            // AttackTargetから対象BattleSideを決定、Foeの場合は逆側に設定
            var targetBattleSide = AttackElement.AttackTarget == AttackTarget.Foe
                ? (TargetSelectionData.AttackerBattleSide == BattleSide.Player ? BattleSide.Enemy : BattleSide.Player)
                : TargetSelectionData.AttackerBattleSide;

            // 陣営に応じて適切なDeckを選択（PVP対応）
            var deckUnits = targetBattleSide == BattleSide.Player
                ? context.PlayerDeckUnits
                : context.PvpOpponentDeckUnits;

            var filteredDeckUnits = deckUnits
                .Where(deckUnit => IsDeckTarget(AttackerCharacterId, targetBattleSide, deckUnit, TargetSelectionData))
                .Take(TargetSelectionData.MaxTargetCount.Value)
                .ToList();

            // AttackElement
            foreach (var deckUnit in filteredDeckUnits)
            {
                if (randomProvider.Trial(AttackElement.Probability))
                {
                    attackResults.Add(
                        attackResultModelFactory.CreateDeckAttackResult(
                            this,
                            AttackElement,
                            deckUnit.CharacterId,
                            targetBattleSide));
                }
            }

            // SubElements
            foreach (var deckUnit in filteredDeckUnits)
            {
                var subElementResults = AttackElement.SubElements
                    .Where(subElement => randomProvider.Trial(subElement.Probability))
                    .Select(subElement =>
                        attackResultModelFactory.CreateDeckAttackResult(
                            this,
                            subElement,
                            deckUnit.CharacterId,
                            targetBattleSide)
                    );

                attackResults.AddRange(subElementResults);
            }

            return attackResults;
        }

        IReadOnlyList<IAttackResultModel> GetRushAttackPowerUpResults(AttackModelContext context)
        {
            var attackResultModelFactory = context.AttackResultModelFactory;
            var randomProvider = context.RandomProvider;
            var attackResults = new List<IAttackResultModel>();

            // AttackElement
            if (randomProvider.Trial(AttackElement.Probability))
            {
                attackResults.Add(attackResultModelFactory.CreateHitAttackResult(this, AttackElement, FieldObjectId.Empty));
            }

            // SubElements
            var attackResultsBySubElement = AttackElement.SubElements
                .Where(subElement => randomProvider.Trial(subElement.Probability))
                .Select(subElement => attackResultModelFactory.CreateHitAttackResult(this, subElement, FieldObjectId.Empty));

            attackResults.AddRange(attackResultsBySubElement);

            return attackResults;
        }

        bool IsDeckTarget(
            MasterDataId attackerCharacterId,
            BattleSide targetBattleSide,
            DeckUnitModel targetDeckUnit,
            AttackTargetSelectionData selectionData)
        {
            // キャラ未設定のDeck除外
            if (targetDeckUnit.IsEmptyUnit()) return false;

            // AttackTarget.Selfの場合は発動者自身のみ対象
            if (selectionData.AttackTarget == AttackTarget.Self)
            {
                return targetDeckUnit.CharacterId == attackerCharacterId;
            }

            // FriendOnlyの場合、発動者CharacterIdを除外
            if (selectionData.AttackTarget == AttackTarget.FriendOnly && targetDeckUnit.CharacterId == attackerCharacterId)
            {
                return false;
            }

            // BattleSide、RoleType、CharacterColor、SeriesId、CharacterIdで絞り込み
            return targetBattleSide == targetDeckUnit.BattleSide
                   && selectionData.TargetRoles.Contains(targetDeckUnit.RoleType)
                   && selectionData.TargetColors.Contains(targetDeckUnit.CharacterColor)
                   && IsTargetSeriesId(selectionData.TargetSeriesIds, targetDeckUnit.MstSeriesId)
                   && IsTargetCharacterId(selectionData.TargetCharacterIds, targetDeckUnit.CharacterId);
        }

        bool IsTargetSeriesId(IReadOnlyList<MasterDataId> targetSeriesIds, MasterDataId seriesId)
        {
            // 空リストの場合は絞り込みなし（全対象）
            return targetSeriesIds.Count == 0 || targetSeriesIds.Contains(seriesId);
        }

        bool IsTargetCharacterId(IReadOnlyList<MasterDataId> targetCharacterIds, MasterDataId characterId)
        {
            // 空リストの場合は絞り込みなし（全対象）
            return targetCharacterIds.Count == 0 || targetCharacterIds.Contains(characterId);
        }
    }
}
