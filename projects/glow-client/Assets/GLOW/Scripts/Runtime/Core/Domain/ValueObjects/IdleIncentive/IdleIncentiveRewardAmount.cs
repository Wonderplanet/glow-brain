using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects.Pass;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.IdleIncentive
{
    public record IdleIncentiveRewardAmount(ObscuredFloat Value)
    {
        public static IdleIncentiveRewardAmount Empty { get; } = new(0);
        public static IdleIncentiveRewardAmount operator *(IdleIncentiveRewardAmount a, PassEffectValue b)
        {
            return new IdleIncentiveRewardAmount(a.Value * b.Value);
        }

        public static IdleIncentiveRewardAmount operator -(IdleIncentiveRewardAmount a, IdleIncentiveRewardAmount b)
        {
            return new IdleIncentiveRewardAmount(a.Value - b.Value);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public string GetCalculatedAmountString()
        {
            var displayValue = Value > 999.999f ? 999.999f : (float)Value;
            return ZString.Format("{0:F3}", displayValue);
        }
    }
}
