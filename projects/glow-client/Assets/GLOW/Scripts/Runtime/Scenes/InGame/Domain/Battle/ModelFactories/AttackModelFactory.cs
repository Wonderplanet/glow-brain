using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class AttackModelFactory : IAttackModelFactory
    {
        readonly AttackIdProvider _attackIdProvider = new();

        public (IAttackModel, IReadOnlyList<IStateEffectModel>) Create(
            FieldObjectId fieldObjectId,
            MasterDataId attackerCharacterId,
            StateEffectSourceId stateEffectSourceId,
            BattleSide battleSide,
            CharacterUnitRoleType roleType,
            CharacterColor color,
            OutpostCoordV2 pos,
            AttackPower baseAttackPower,
            HealPower healPower,
            CharacterColorAdvantageAttackBonus colorAdvantageAttackBonus,
            AttackBaseData attackBaseData,
            AttackElement attackElement,
            IReadOnlyList<IStateEffectModel> effects,
            MstPageModel mstPageModel,
            ICoordinateConverter coordinateConverter,
            IBuffStatePercentageConverter buffStatePercentageConverter)
        {
            // バフ、デバフ
            var updatedEffects = effects;

            var buffPercentages = buffStatePercentageConverter.ReduceBuffStateEffectCountAndGetPercentages(
                updatedEffects,
                out var updatedEffectsByBuff);
            updatedEffects = updatedEffectsByBuff;

            var debuffPercentages = buffStatePercentageConverter.ReduceDebuffStateEffectCountAndGetPercentages(
                updatedEffects,
                out var updatedEffectsByDebuff);
            updatedEffects = updatedEffectsByDebuff;

            // AttackModelを作成
            var attack = Create(
                fieldObjectId,
                attackerCharacterId,
                stateEffectSourceId,
                battleSide,
                roleType,
                color,
                pos,
                baseAttackPower,
                healPower,
                colorAdvantageAttackBonus,
                attackBaseData,
                attackElement,
                buffPercentages,
                debuffPercentages,
                mstPageModel,
                coordinateConverter);

            return (attack, updatedEffects);
        }

        IAttackModel Create(
            FieldObjectId fieldObjectId,
            MasterDataId attackerCharacterId,
            StateEffectSourceId stateEffectSourceId,
            BattleSide battleSide,
            CharacterUnitRoleType roleType,
            CharacterColor color,
            OutpostCoordV2 pos,
            AttackPower baseAttackPower,
            HealPower healPower,
            CharacterColorAdvantageAttackBonus colorAdvantageAttackBonus,
            AttackBaseData attackBaseData,
            AttackElement attackElement,
            IReadOnlyList<PercentageM> buffPercentages,
            IReadOnlyList<PercentageM> debuffPercentages,
            MstPageModel mstPageModel,
            ICoordinateConverter coordinateConverter)
        {
            // AttackModel作成
            IAttackModel attack = attackElement.AttackType switch
            {
                AttackType.Direct => new RangeAttackModel(
                    _attackIdProvider.GenerateNewId(),
                    fieldObjectId,
                    stateEffectSourceId,
                    attackElement,
                    roleType,
                    color,
                    attackBaseData.KillerColors,
                    attackBaseData.KillerPercentage,
                    baseAttackPower,
                    healPower,
                    colorAdvantageAttackBonus,
                    buffPercentages,
                    debuffPercentages,
                    attackElement.HitDelay,
                    CreateAttackTargetSelectionData(
                        fieldObjectId,
                        battleSide,
                        pos,
                        attackElement,
                        mstPageModel,
                        coordinateConverter),
                    false),

                AttackType.Deck => new DeckAttackModel(
                    _attackIdProvider.GenerateNewId(),
                    fieldObjectId,
                    attackerCharacterId,
                    stateEffectSourceId,
                    attackElement,
                    roleType,
                    color,
                    attackBaseData.KillerColors,
                    attackBaseData.KillerPercentage,
                    baseAttackPower,
                    healPower,
                    colorAdvantageAttackBonus,
                    buffPercentages,
                    debuffPercentages,
                    attackElement.HitDelay,
                    CreateAttackTargetSelectionDataForDeck(
                        fieldObjectId,
                        battleSide,
                        attackElement),
                    false),

                AttackType.PlaceItem => new PlaceItemAttackModel(
                    _attackIdProvider.GenerateNewId(),
                    fieldObjectId,
                    battleSide,
                    attackElement,
                    attackElement.HitDelay,
                    false),

                AttackType.None => new EmptyAttackModel(),
                _ =>  new EmptyAttackModel()
            };

            return attack;
        }

        AttackTargetSelectionData CreateAttackTargetSelectionData(
            FieldObjectId fieldObjectId,
            BattleSide battleSide,
            OutpostCoordV2 pos,
            AttackElement attackElement,
            MstPageModel mstPageModel,
            ICoordinateConverter coordinateConverter)
        {
            var fieldCoordRange = AttackRangeConverter.ToFieldCoordAttackRange(
                battleSide,
                pos,
                attackElement.AttackRange,
                mstPageModel,
                coordinateConverter);

            var attackTargetSelectionData = new AttackTargetSelectionData(
                fieldObjectId,
                battleSide,
                attackElement.AttackTarget,
                attackElement.AttackTargetType,
                attackElement.TargetColors,
                attackElement.TargetRoles,
                attackElement.AttackDamageType == AttackDamageType.Heal,
                attackElement.MaxTargetCount,
                fieldCoordRange);

            return attackTargetSelectionData;
        }

        AttackTargetSelectionData CreateAttackTargetSelectionDataForDeck(
            FieldObjectId fieldObjectId,
            BattleSide battleSide,
            AttackElement attackElement)
        {
            var attackTargetSelectionData = new AttackTargetSelectionData(
                fieldObjectId,
                battleSide,
                attackElement.AttackTarget,
                attackElement.AttackTargetType,
                attackElement.TargetColors,
                attackElement.TargetRoles,
                attackElement.AttackDamageType == AttackDamageType.Heal,
                attackElement.MaxTargetCount,
                CoordinateRange.Empty);

            return attackTargetSelectionData;
        }
    }
}
