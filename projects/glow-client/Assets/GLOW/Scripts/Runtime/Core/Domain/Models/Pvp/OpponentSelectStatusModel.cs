using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record OpponentSelectStatusModel(
        UserMyId MyId,
        UserName Name,
        MasterDataId MstUnitId,
        MasterDataId MstEmblemId,
        PvpPoint Score,
        OpponentPvpStatusModel OpponentSelectStatus,
        PvpPoint WinAddPoint
    )
    {
        public static OpponentSelectStatusModel Empty { get; } = new (
            UserMyId.Empty,
            UserName.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            PvpPoint.Empty,
            OpponentPvpStatusModel.Empty,
            PvpPoint.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

