using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ArtworkGradeLevel(ObscuredInt Value) : IComparable
    {
        public static ArtworkGradeLevel Empty { get; } = new ArtworkGradeLevel(0);

        public static ArtworkGradeLevel GetRequiredGradeLevel(ArtworkGradeLevel targetGradeLevel)
        {
            if (targetGradeLevel.IsEmpty()) return Empty;

            var requiredGradeLevel = targetGradeLevel.Value - 1;
            if (requiredGradeLevel < 1) return Empty;

            return new ArtworkGradeLevel(requiredGradeLevel);
        }

        public static implicit operator int(ArtworkGradeLevel gradeLevel) => gradeLevel.Value;

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public ArtworkGradeLevel GetPrevGradeLevel()
        {
            if(IsEmpty()) return Empty;

            var prevGradeLevel = Value - 1;
            if (prevGradeLevel < 1) return new ArtworkGradeLevel(Value);

            return new ArtworkGradeLevel(prevGradeLevel);
        }

        public ArtworkGradeLevel GetNextGradeLevel()
        {
            if (IsEmpty()) return Empty;

            return new ArtworkGradeLevel(Value + 1);
        }

        public int CompareTo(object obj)
        {
            if (obj is ArtworkGradeLevel other)
            {
                return Value.CompareTo(other.Value);
            }

            return -1;
        }
    }
}
