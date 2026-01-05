using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AttackRange(
        AttackRangePointType StartPointType,
        AttackRangeParameter StartPointParameter,
        AttackRangePointType EndPointType,
        AttackRangeParameter EndPointParameter)
    {
        public static AttackRange Empty { get; } = new (
            AttackRangePointType.Distance,
            AttackRangeParameter.Empty,
            AttackRangePointType.Distance,
            AttackRangeParameter.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
