using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.Models
{
    public record MstOutpostModel(
        MasterDataId Id,
        OutpostImageAssetKey AssetKey,
        IReadOnlyList<MstOutpostEnhancementModel> EnhancementModels,
        ObscuredDateTimeOffset StartDate,
        ObscuredDateTimeOffset EndDate)
    {
        public static MstOutpostModel Empty { get; } = new(
            MasterDataId.Empty,
            OutpostImageAssetKey.Empty,
            new List<MstOutpostEnhancementModel>(),
            DateTimeOffset.MinValue,
            DateTimeOffset.MinValue
        );
    }
}
