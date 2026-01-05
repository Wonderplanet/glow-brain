using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Gacha
{
    public record GachaHistoryModel(
        MasterDataId OprGachaId,
        CostType CostType,
        MasterDataId MstCostId,
        CostAmount CostAmount,
        DateTimeOffset PlayedAt,
        IReadOnlyList<GachaHistoryRewardModel> GachaHistoryRewardModels);
}