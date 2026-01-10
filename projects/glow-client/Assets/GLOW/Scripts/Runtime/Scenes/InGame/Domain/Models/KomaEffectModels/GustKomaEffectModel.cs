using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record GustKomaEffectModel(
            KomaId KomaId,
            KomaEffectType EffectType,
            KomaEffectTargetSide TargetSide,
            IReadOnlyList<CharacterColor> TargetColors,
            IReadOnlyList<CharacterUnitRoleType> TargetRoles,
            KomaEffectParameter GustInterval,
            KomaEffectParameter SlideDistance,
            TickCount RemainingGustInterval,
            TickCount RemainingGustDuration,
            GustEffectDirection GustEffectDirection)
        : BaseKomaEffectModel(
            KomaId,
            EffectType,
            TargetSide,
            TargetColors,
            TargetRoles)
    {
        const float SlideSpeed = 0.02f;
        static readonly List<StateEffectType> StateEffectsThatBlockableThis = new() { StateEffectType.GustKomaBlock };

        public override IReadOnlyList<StateEffectType> GetStateEffectsThatBlockableThis()
        {
            return StateEffectsThatBlockableThis;
        }

        public override IKomaEffectModel GetUpdatedModel(KomaEffectUpdateContext context)
        {
            var tickCount = context.TickCount;

            var remainingGustDuration = RemainingGustInterval.IsZero()
                ? GetGustDuration()
                : RemainingGustDuration - tickCount;

            var remainingGustInterval = RemainingGustInterval.IsZero()
                ? GustInterval.ToTickCount()
                : RemainingGustInterval - tickCount;
            
            var nextGustEffectDirection = GustEffectDirection;
            if (ShouldChangeGustEffectDirection(remainingGustDuration))
            {
                // 突風の向きを反転させる
                nextGustEffectDirection = nextGustEffectDirection == GustEffectDirection.ToPlayer
                    ? GustEffectDirection.ToEnemy
                    : GustEffectDirection.ToPlayer;
            }

            return this with
            {
                RemainingGustInterval = remainingGustInterval,
                RemainingGustDuration = remainingGustDuration,
                GustEffectDirection = nextGustEffectDirection,
            };
        }

        public override (IReadOnlyList<CharacterUnitModel>, IReadOnlyList<FieldObjectId>) AffectCharacterUnits(
            IReadOnlyList<CharacterUnitModel> characterUnitModels,
            MstPageModel mstPageModel,
            ICoordinateConverter coordinateConverter,
            IStateEffectChecker stateEffectChecker)
        {
            if (RemainingGustDuration.IsZero())
            {
                return (characterUnitModels, EmptyFieldObjectIdList);
            }

            var komaRange = mstPageModel.GetKomaRange(KomaId);

            var updatedCharacterUnitModels = new List<CharacterUnitModel>();
            var blockedCharacterUnitIds = new List<FieldObjectId>();

            foreach (var unit in characterUnitModels)
            {
                if (unit.LocatedKoma.Id != KomaId || !IsTarget(unit) ||
                    unit.Action.ActionState == UnitActionState.InterruptSlide)
                {
                    updatedCharacterUnitModels.Add(unit);
                    continue;
                }

                // 状態変化による無効化
                var canBlock = StateEffectsThatBlockableThis
                    .Any(stateEffect => stateEffectChecker.Check(stateEffect, unit.StateEffects));

                if (canBlock)
                {
                    updatedCharacterUnitModels.Add(unit);

                    // 突風の開始時か、突風中にコマに入ってきたタイミングだけ、ブロック判定とする
                    if (RemainingGustDuration == GetGustDuration() || unit.LocatedKoma.Id != unit.PrevLocatedKoma.Id)
                    {
                        blockedCharacterUnitIds.Add(unit.Id);
                    }

                    continue;
                }

                // InterruptSlideActionに移行できるかチェック
                if (!unit.Action.CanForceChangeTo(UnitActionState.InterruptSlide))
                {
                    updatedCharacterUnitModels.Add(unit);
                    continue;
                }

                // 突風で後退させる距離の求める
                // 最大でも突風コマの端の少し先まで
                var unitFieldCoordPos = coordinateConverter.OutpostToFieldCoord(unit.BattleSide, unit.Pos);
                var maxSlideDistance = unitFieldCoordPos.X - komaRange.Min + 0.1f; // コマ端の少し先まで

                var baseSlideDistance = SlideDistance.ToFloat();
                var slideDistance = Mathf.Min(baseSlideDistance, maxSlideDistance);
                var slideDuration = new TickCount(Mathf.CeilToInt(slideDistance / SlideSpeed));

                var action = slideDuration.IsZero()
                    ? unit.Action
                    : new CharacterUnitInterruptSlideAction(slideDuration, slideDistance, unit.Action);

                updatedCharacterUnitModels.Add(unit with
                {
                    Action = action,
                    PrevActionState = unit.Action.ActionState
                });
            }

            return (updatedCharacterUnitModels, blockedCharacterUnitIds);
        }

        protected override bool IsTargetBattleSide(BattleSide battleSide)
        {
            return GustEffectDirection switch
            {
                GustEffectDirection.ToPlayer => battleSide == BattleSide.Player,
                GustEffectDirection.ToEnemy => battleSide == BattleSide.Enemy,
                _ => false
            };
        }

        bool ShouldChangeGustEffectDirection(TickCount updatedRemainingGustDuration)
        {
            return TargetSide == KomaEffectTargetSide.All &&
                   !RemainingGustDuration.IsZero() &&
                   updatedRemainingGustDuration.IsZero();
        }

        TickCount GetGustDuration()
        {
            return new TickCount(Mathf.CeilToInt(SlideDistance.ToFloat() / SlideSpeed));
        }
    }
}
