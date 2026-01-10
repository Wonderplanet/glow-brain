using System.Collections.Generic;

namespace GLOW.Debugs.Home.Domain.Models
{
    public record OverrideAttackElement(
        float NormalAttackDelay,
        float NormalAttackDuration,
        float NormalTotalDuration,
        float SpecialAttackDelay,
        float SpecialAttackDuration,
        float SpecialTotalDuration,
        IReadOnlyList<(bool, float, float)> NormalAttackElements,
        IReadOnlyList<(bool, float, float)> SpecialAttackElements)
    {
        public static OverrideAttackElement Empty => new OverrideAttackElement(
            0f,
            0f,
            0f,
            0f,
            0f,
            0f,
            new List<(bool, float, float)>(),
            new List<(bool, float, float)>()
        );
    }
}
