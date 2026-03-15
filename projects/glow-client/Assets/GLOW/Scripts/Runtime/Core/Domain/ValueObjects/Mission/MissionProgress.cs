using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record MissionProgress(ObscuredInt Value)
    {
        public static MissionProgress Empty { get; } = new(0);
        public static float operator /(MissionProgress a, CriterionCount b)
        {
            return (float)a.Value / b.Value;
        }
        
        public string ToStringSeparated()
        {
            return ZString.Format("{0:N0}", Value);
        }
       
    }
}
