using System;
using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.Models
{
    public record UserConditionPackModel(MasterDataId MstPackId, ObscuredDateTimeOffset StartDate)
    {
        public static UserConditionPackModel Empty { get; } = new (MasterDataId.Empty, DateTimeOffset.MinValue);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
