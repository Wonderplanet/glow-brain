using System.Collections.Generic;

namespace GLOW.Scenes.GachaHistoryDialog.Domain.Models
{
    public record GachaHistoryUseCaseModel(
        IReadOnlyList<GachaHistoryCellModel> GachaHistoryCellModels,
        IReadOnlyList<GachaHistoryDetailModel> GachaHistoryDetailModels)
    {
        public static GachaHistoryUseCaseModel Empty { get; } =
            new(new List<GachaHistoryCellModel>(), new List<GachaHistoryDetailModel>());
    }
}