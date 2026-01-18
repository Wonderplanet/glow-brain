using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpBonusPointConditionValue(ObscuredString Value)
    {
        public static PvpBonusPointConditionValue Empty { get; } = new PvpBonusPointConditionValue(string.Empty);
        
        public StageClearTime ToStageClearTime()
        {
            return new StageClearTime(TimeSpan.FromMilliseconds(int.Parse(Value)));
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}