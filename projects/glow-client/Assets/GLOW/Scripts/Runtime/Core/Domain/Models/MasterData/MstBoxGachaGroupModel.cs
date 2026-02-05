using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;

namespace GLOW.Core.Domain.Models
{
    public record MstBoxGachaGroupModel(
        MasterDataId MstBoxGachaId,
        BoxLevel BoxLevel,
        IReadOnlyList<MstBoxGachaPrizeModel> Prizes)
    {
        public static MstBoxGachaGroupModel Empty { get; } = new(
            MasterDataId.Empty,
            BoxLevel.Empty,
            new List<MstBoxGachaPrizeModel>()
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}