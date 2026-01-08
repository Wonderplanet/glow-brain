using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Gacha
{
    public record GachaHistoryResultModel(IReadOnlyList<GachaHistoryModel> GachaHistoryModels);
}