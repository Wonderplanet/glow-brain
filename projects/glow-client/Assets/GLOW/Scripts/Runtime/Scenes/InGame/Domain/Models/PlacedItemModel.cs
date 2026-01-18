using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Extensions;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record PlacedItemModel(
        FieldObjectId PlacedItemId,
        BattleSide PlacedItemBattleSide,
        KomaId KomaId,
        FieldCoordV2 Pos,
        AttackElement PickUpAttackElement,
        PlaceItemState PlaceItemState)
    {
        const float PickUpRange = 0.08f;
        
        public static PlacedItemModel Empty { get; } = new(
            FieldObjectId.Empty,
            BattleSide.Player,
            KomaId.Empty,
            FieldCoordV2.Empty,
            AttackElement.Empty, 
            PlaceItemState.Placing);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public (PlacedItemModel, IReadOnlyList<IAttackResultModel>) ExecuteAttack(AttackModelContext context)
        {
            // 効果が発動可能でない場合は何もしない
            if (PlaceItemState != PlaceItemState.EffectAvailable) return (this, new List<IAttackResultModel>());
            
            // 効果対象を取得(ノックバック中のユニットは対象外)
            var targetUnits = context.AttackTargetCandidates
                .OfType<CharacterUnitModel>()
                .Where(target => !target.Action.ActionState.IsKnockBackState())
                .Where(target => !target.PrevActionState.IsKnockBackState())
                .Where(target => IsTarget(target.BattleSide, PickUpAttackElement.AttackTarget))
                .ToList();
            
            var pickerUnitId = GetPickerUnitId(this, targetUnits, context.CoordinateConverter);
            
            // 効果対象がいない場合は何もしない
            if (pickerUnitId.IsEmpty()) return (this, new List<IAttackResultModel>());
            
            var attackResultModelFactory = context.AttackResultModelFactory;
            
            var attackResult = attackResultModelFactory.CreatePickUpItemHitAttackResult(
                PickUpAttackElement,
                PlacedItemId,
                pickerUnitId);
            
            var updatedPlacedItem = this with { PlaceItemState = PlaceItemState.EffectConsumed };
            return (updatedPlacedItem, new List<IAttackResultModel> { attackResult });
        }
        
        FieldObjectId GetPickerUnitId(
            PlacedItemModel placedItem, 
            IReadOnlyList<CharacterUnitModel> characterUnits,
            ICoordinateConverter coordinateConverter)
        {
            foreach (var unit in characterUnits)
            {
                var fieldCoordPos = coordinateConverter.OutpostToFieldCoord(unit.BattleSide, unit.Pos);
                var pickUpRangeMin = fieldCoordPos.X - PickUpRange;
                var pickUpRangeMax = fieldCoordPos.X + PickUpRange;
                var range = CoordinateRange.BetweenPoints(pickUpRangeMax, pickUpRangeMin);
                
                if (range.IsInRange(placedItem.Pos.X))
                {
                    return unit.Id;
                }
            }

            return FieldObjectId.Empty;
        }
        
        bool IsTarget(BattleSide targetBattleSide, AttackTarget attackTarget)
        {
            var battleSide = GetTargetBattleSide(targetBattleSide, attackTarget);
            return battleSide == PlacedItemBattleSide;
        }
        
        BattleSide GetTargetBattleSide(BattleSide targetBattleSide, AttackTarget target)
        {
            // 対象がFriend -> 
            // 対象がFoe -> 
            return target == AttackTarget.Friend
                ? targetBattleSide
                : targetBattleSide == BattleSide.Player ? BattleSide.Enemy : BattleSide.Player;
        }
    }
}