using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Core.Domain.Models
{
    public record MngContentCloseModel(
        MasterDataId Id,
        ContentMaintenanceType ContentMaintenanceType,
        MasterDataId ContentId,
        DateTimeOffset StartAt,
        DateTimeOffset EndAt
        )
    {

        public static MngContentCloseModel Empty { get; } = new MngContentCloseModel(
            MasterDataId.Empty,
            ContentMaintenanceType.Non,
            MasterDataId.Empty,
            DateTimeOffset.MinValue,
            DateTimeOffset.MinValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
