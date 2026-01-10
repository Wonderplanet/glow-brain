using GLOW.Core.Domain.ValueObjects.Campaign;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record ClearableCount(ObscuredInt Value)
    {
        public static ClearableCount Empty { get; } = new(-1);
        public bool IsEmpty() => ReferenceEquals(this, Empty);
        public static bool operator >(ClearableCount left, ClearableCount right) => left.Value > right.Value;
        public static bool operator <(ClearableCount left, ClearableCount right) => left.Value < right.Value;
        public static bool operator >=(ClearableCount left, ClearableCount right) => left.Value >= right.Value;
        public static bool operator <=(ClearableCount left, ClearableCount right) => left.Value <= right.Value;

        public static bool operator >(StageClearCount left, ClearableCount right) => left.Value > right.Value;
        public static bool operator <(StageClearCount left, ClearableCount right) => left.Value < right.Value;
        public static bool operator >=(StageClearCount left, ClearableCount right) => left.Value >= right.Value;
        public static bool operator <=(StageClearCount left, ClearableCount right) => left.Value <= right.Value;

        public static bool operator >(ClearableCount left, StageClearCount right) => left.Value > right.Value;
        public static bool operator <(ClearableCount left, StageClearCount right) => left.Value < right.Value;
        public static bool operator >=(ClearableCount left, StageClearCount right) => left.Value >= right.Value;
        public static bool operator <=(ClearableCount left, StageClearCount right) => left.Value <= right.Value;
        
        public static ClearableCount operator +(ClearableCount a, CampaignEffectValue b)
        {
            return new ClearableCount(a.Value + b.Value);
        }
    };
}
