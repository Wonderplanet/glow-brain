using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AttackElement(
        MasterDataId Id,
        TickCount AttackDelay, // 攻撃が発生するまでの時間（それまでに攻撃モーションがキャンセルされると攻撃が発生しない）
        TickCount HitDelay, // 攻撃が発生してからヒット判定がされるまでの時間
        AttackType AttackType,
        AttackRange AttackRange,
        FieldObjectCount MaxTargetCount,
        AttackViewId AttackViewId,
        AttackTarget AttackTarget,
        AttackTargetType AttackTargetType,
        IReadOnlyList<CharacterColor> TargetColors,
        IReadOnlyList<CharacterUnitRoleType> TargetRoles,
        AttackDamageType AttackDamageType,
        AttackHitData AttackHitData,
        AttackHitStopFlag IsHitStop,
        Percentage Probability,
        AttackPowerParameter PowerParameter,
        StateEffect StateEffect,
        IReadOnlyList<AttackSubElement> SubElements)
    {
        public static AttackElement Empty { get; } = new(
            MasterDataId.Empty,
            TickCount.Empty,
            TickCount.Empty,
            AttackType.None,
            AttackRange.Empty,
            FieldObjectCount.Empty,
            AttackViewId.Empty,
            AttackTarget.Foe,
            AttackTargetType.All,
            Array.Empty<CharacterColor>(),
            Array.Empty<CharacterUnitRoleType>(),
            AttackDamageType.None,
            AttackHitData.Empty,
            AttackHitStopFlag.False,
            Percentage.Empty,
            AttackPowerParameter.Empty,
            StateEffect.Empty,
            Array.Empty<AttackSubElement>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public virtual bool Equals(AttackElement other)
        {
            if (ReferenceEquals(this, other)) return true;
            if (other == null) return false;

            if (AttackDelay != other.AttackDelay) return false;
            if (HitDelay != other.HitDelay) return false;
            if (AttackType != other.AttackType) return false;
            if (AttackRange != other.AttackRange) return false;
            if (MaxTargetCount != other.MaxTargetCount) return false;
            if (AttackViewId != other.AttackViewId) return false;
            if (AttackTarget != other.AttackTarget) return false;
            if (AttackTargetType != other.AttackTargetType) return false;
            if (AttackDamageType != other.AttackDamageType) return false;
            if (!AttackHitData.Equals(other.AttackHitData)) return false;
            if (IsHitStop != other.IsHitStop) return false;
            if (Probability != other.Probability) return false;
            if (!PowerParameter.Equals(other.PowerParameter)) return false;
            if (!StateEffect.Equals(other.StateEffect)) return false;

            if ((TargetColors == null) ^ (other.TargetColors == null)) return false;
            if (TargetColors != null && other.TargetColors != null)
            {
                if (!TargetColors.SequenceEqual(other.TargetColors)) return false;
            }

            if ((TargetRoles == null) ^ (other.TargetRoles == null)) return false;
            if (TargetRoles != null && other.TargetRoles != null)
            {
                if (!TargetRoles.SequenceEqual(other.TargetRoles)) return false;
            }

            if ((SubElements == null) ^ (other.SubElements == null)) return false;
            if (SubElements != null && other.SubElements != null)
            {
                if (!SubElements.SequenceEqual(other.SubElements)) return false;
            }

            return true;
        }

        public override int GetHashCode()
        {
            HashCode hash = new();

            hash.Add(AttackDelay);
            hash.Add(HitDelay);
            hash.Add(AttackType);
            hash.Add(AttackRange);
            hash.Add(MaxTargetCount);
            hash.Add(AttackViewId);
            hash.Add(AttackTarget);
            hash.Add(AttackTargetType);
            hash.Add(AttackDamageType);
            hash.Add(AttackHitData);
            hash.Add(IsHitStop);
            hash.Add(Probability);
            hash.Add(PowerParameter);
            hash.Add(StateEffect);

            AddHashCodes(hash, TargetColors);
            AddHashCodes(hash, TargetRoles);
            AddHashCodes(hash, SubElements);

            return hash.ToHashCode();
        }

        static void AddHashCodes<T>(HashCode hash, IReadOnlyList<T> models)
        {
            if (models == null) return;

            int start = models.Count > 10 ? models.Count - 10 : 0;
            for (int i = start; i < models.Count; i++)
            {
                hash.Add(models[i]);
            }
        }
    }
}
