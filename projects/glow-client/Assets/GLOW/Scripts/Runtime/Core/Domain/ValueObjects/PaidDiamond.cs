using System.Globalization;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PaidDiamond(ObscuredInt Value) : ILimitedAmountValueObject
    {
        public static PaidDiamond Empty { get; } = new(0);

        public int HasAmount => Value;

        public static TotalDiamond operator +(PaidDiamond a, FreeDiamond b)
        {
            return new TotalDiamond(a.Value + b.Value);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public string ToStringSeparated()
        {
            return HasAmount.ToString("N0", CultureInfo.InvariantCulture);
        }

        public string ToStringWithMultiplication()
        {
            return ZString.Format("×{0}", HasAmount);
        }
    }
}
