using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;

namespace GLOW.Core.Domain.Models
{
    public record UserBoxGachaModel(
        MasterDataId MstBoxGachaId,
        BoxResetCount ResetCount,
        BoxDrawCount TotalDrawCount,
        BoxLevel CurrentBoxLevel,
        IReadOnlyList<UserBoxGachaPrizeModel> UserBoxGachaPrizeModels)
    {
        public static UserBoxGachaModel Empty { get; } = new(
            MasterDataId.Empty,
            BoxResetCount.Empty,
            BoxDrawCount.Empty,
            BoxLevel.Empty,
            new List<UserBoxGachaPrizeModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}