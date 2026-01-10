using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record FreeDiamond(ObscuredInt Value): ILimitedAmountValueObject
    {
        public int HasAmount => Value;
        public static FreeDiamond Empty { get; } = new (0);
        public string ToStringSeparated()
        {
            return HasAmount.ToString("N0");
        }

        public string ToStringWithMultiplication()
        {
            return ZString.Format("×{0}", HasAmount);
        }
    }
}
