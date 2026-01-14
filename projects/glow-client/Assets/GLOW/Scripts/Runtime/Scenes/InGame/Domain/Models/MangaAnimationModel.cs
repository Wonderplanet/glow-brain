using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record MangaAnimationModel(
        ActivatedMangaAnimationFlag IsActivated,
        MangaAnimationConditionType ConditionType,
        MangaAnimationConditionValue ConditionValue,
        TickCount AnimationStartDelay,
        TickCount RemainingAnimationStartDelay,
        MangaAnimationSpeed AnimationSpeed,
        MangaAnimationBattlePauseFlag IsPause,
        MangaAnimationSkipPossibleFlag CanSkip,
        MangaAnimationAssetKey AssetKey)
    {
        public static MangaAnimationModel Empty { get; } = new(
            ActivatedMangaAnimationFlag.False,
            MangaAnimationConditionType.None,
            MangaAnimationConditionValue.Empty,
            TickCount.Empty,
            TickCount.Empty,
            MangaAnimationSpeed.Empty,
            MangaAnimationBattlePauseFlag.False,
            MangaAnimationSkipPossibleFlag.False,
            MangaAnimationAssetKey.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool MeetsCondition(IReadOnlyList<CharacterUnitModel> units)
        {
            if (IsActivated) return false;

            if (ConditionType == MangaAnimationConditionType.EnemySummon)
            {
                var autoPlayerSequenceElementId = ConditionValue.ToAutoPlayerSequenceElementId();
                return units.Any(unit => unit.AutoPlayerSequenceElementId == autoPlayerSequenceElementId);
            }

            if (ConditionType == MangaAnimationConditionType.EnemyMoveStart)
            {
                var autoPlayerSequenceElementId = ConditionValue.ToAutoPlayerSequenceElementId();

                return units
                    .Where(unit => unit.AutoPlayerSequenceElementId == autoPlayerSequenceElementId)
                    .Any(unit => unit.IsMoveStarted);
            }

            if (ConditionType == MangaAnimationConditionType.TransformationReady ||
                ConditionType == MangaAnimationConditionType.TransformationStart ||
                ConditionType == MangaAnimationConditionType.TransformationEnd)
            {
                // 変身時の原画演出はすべて変身演出中（ステージ進行は停止）に出すので、変身が開始されたら原画演出もすべて開始させる
                var autoPlayerSequenceElementId = ConditionValue.ToAutoPlayerSequenceElementId();

                return units
                    .Where(unit => unit.AutoPlayerSequenceElementId == autoPlayerSequenceElementId)
                    .Any(unit => unit.Action.ActionState == UnitActionState.Transformation);
            }

            return false;
        }

        public bool NeedsImmediatePlay()
        {
            return ConditionType != MangaAnimationConditionType.TransformationReady &&
                   ConditionType != MangaAnimationConditionType.TransformationStart &&
                   ConditionType != MangaAnimationConditionType.TransformationEnd;
        }
    }
}
