using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class AttackResultModelFactory : IAttackResultModelFactory
    {
        [Inject] IRandomProvider RandomProvider { get; }

        public HitAttackResultModel CreateHitAttackResult(IAttackModel attack, AttackElement attackElement, FieldObjectId targetId)
        {
            var attachHitData = DecideHitType(attackElement.AttackHitData);

            return new HitAttackResultModel(
                targetId,
                attack.AttackerId,
                attackElement.AttackDamageType,
                attachHitData,
                attackElement.IsHitStop,
                attack.AttackerRoleType,
                attack.AttackerColor,
                attack.KillerColors,
                attack.KillerPercentage,
                attack.BasePower,
                attackElement.PowerParameter,
                attack.HealPower,
                attack.ColorAdvantageAttackBonus,
                attack.BuffPercentages,
                attack.DebuffPercentages,
                attack.AttackerStateEffectSourceId,
                attackElement.StateEffect);
        }

        public HitAttackResultModel CreateHitAttackResult(IAttackModel attack, AttackSubElement attackSubElement, FieldObjectId targetId)
        {
            var attachHitData = DecideHitType(attackSubElement.AttackHitData);

            return new HitAttackResultModel(
                targetId,
                attack.AttackerId,
                attackSubElement.AttackDamageType,
                attachHitData,
                AttackHitStopFlag.False,
                attack.AttackerRoleType,
                attack.AttackerColor,
                attack.KillerColors,
                attack.KillerPercentage,
                attack.BasePower,
                attackSubElement.PowerParameter,
                attack.HealPower,
                attack.ColorAdvantageAttackBonus,
                attack.BuffPercentages,
                attack.DebuffPercentages,
                attack.AttackerStateEffectSourceId,
                attackSubElement.StateEffect);
        }

        public HitAttackResultModel CreatePickUpItemHitAttackResult(AttackElement attackElement, FieldObjectId placedItemId, FieldObjectId targetId)
        {
            var attachHitData = DecideHitType(attackElement.AttackHitData);

            return new HitAttackResultModel(
                targetId,
                placedItemId,
                attackElement.AttackDamageType,
                attachHitData,
                AttackHitStopFlag.False,
                CharacterUnitRoleType.None,
                CharacterColor.None,
                new List<CharacterColor>(),
                KillerPercentage.Empty,
                AttackPower.Empty,
                attackElement.PowerParameter,
                HealPower.Default,
                CharacterColorAdvantageAttackBonus.Empty,
                new List<PercentageM>(),
                new List<PercentageM>(),
                StateEffectSourceId.Empty,
                attackElement.StateEffect);
        }

        public PlacedItemAttackResultModel CreatePlacedItem(
            IAttackModel attack, 
            BattleSide placedItemBattleSide, 
            KomaId placedItemKomaId,
            FieldCoordV2 placedItemPos,
            AttackElement attackElement)
        {
            return new PlacedItemAttackResultModel(
                attack.AttackerId,
                placedItemBattleSide,
                placedItemKomaId,
                placedItemPos,
                attackElement with
                {
                    IsHitStop = AttackHitStopFlag.False,
                    AttackDelay = TickCount.Empty,
                    HitDelay = TickCount.Empty,
                    AttackType = AttackType.Direct
                },
                StateEffect.Empty);
        }

        public DeckAttackResultModel CreateDeckAttackResult(
            IAttackModel attack,
            AttackElement attackElement,
            MasterDataId targetCharacterId,
            BattleSide targetBattleSide)
        {

            return new DeckAttackResultModel(
                targetCharacterId,
                targetBattleSide,
                attack.AttackerId,
                attackElement.StateEffect);
        }

        public DeckAttackResultModel CreateDeckAttackResult(
            IAttackModel attack,
            AttackSubElement attackSubElement,
            MasterDataId targetCharacterId,
            BattleSide targetBattleSide)
        {
            return new DeckAttackResultModel(
                targetCharacterId,
                targetBattleSide,
                attack.AttackerId,
                attackSubElement.StateEffect);
        }

        AttackHitData DecideHitType(AttackHitData attackHitData)
        {
            // HP吸収の場合は指定確率でHP吸収、それ以外はNormalヒットになる
            if (attackHitData.HitType == AttackHitType.Drain)
            {
                var probability = attackHitData.HitParameter2.ToPercentage();
                var isDrain = RandomProvider.Trial(probability);

                return isDrain ? attackHitData : AttackHitData.Normal;
            }

            // スタンの場合は指定確率でスタン、それ以外はNormalヒットになる
            if (attackHitData.HitType == AttackHitType.Stun)
            {
                var probability = attackHitData.HitParameter2.ToPercentage();
                var isStun = RandomProvider.Trial(probability);

                return isStun ? attackHitData : AttackHitData.Normal;
            }

            // 氷結の場合は指定確率で氷結、それ以外はNormalヒットになる
            if (attackHitData.HitType == AttackHitType.Freeze)
            {
                var probability = attackHitData.HitParameter2.ToPercentage();
                var isFreeze = RandomProvider.Trial(probability);

                return isFreeze ? attackHitData : AttackHitData.Normal;
            }

            return attackHitData;
        }
    }
}
