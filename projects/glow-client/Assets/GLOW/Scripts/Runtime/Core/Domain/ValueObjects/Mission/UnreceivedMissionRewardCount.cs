using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record UnreceivedMissionRewardCount(ObscuredInt Value)
    {
        public static UnreceivedMissionRewardCount Empty { get; } = new(0);

        public ObscuredInt Value { get; } = Value > 0 ? Value : 0;
        
        public static UnreceivedMissionRewardCount operator +(UnreceivedMissionRewardCount a, UnreceivedMissionRewardCount b)
        {
            return new UnreceivedMissionRewardCount(a.Value + b.Value);
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public bool IsZero()
        {
            return Value == 0;
        }
    }
}