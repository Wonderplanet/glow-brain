using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record AdGachaDrawableCount(ObscuredInt Value) : IGachaCountableValueObject
    {
        public static AdGachaDrawableCount Zero { get; } = new(0);
        
        public string ToRemainingCountString()
        {
            return ZString.Format("あと{0}回", Value);
        }
    }
}