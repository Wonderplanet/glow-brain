using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstUnitRankCoefficientModel(
        UnitRank Rank,
        UnitRankCoefficient Coefficient,
        UnitRankCoefficient SpecialUnitCoefficient
        );
}
