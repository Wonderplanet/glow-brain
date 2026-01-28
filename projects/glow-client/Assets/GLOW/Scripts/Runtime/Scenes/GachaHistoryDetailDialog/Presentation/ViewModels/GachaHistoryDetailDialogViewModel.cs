using System.Collections.Generic;

namespace GLOW.Scenes.GachaHistoryDetailDialog.Presentation.ViewModels
{
    public record GachaHistoryDetailDialogViewModel(
        IReadOnlyList<GachaHistoryDetailCellViewModel> GachaHistoryDetailCellViewModels)
    {
        public static GachaHistoryDetailDialogViewModel Empty { get; } = 
            new(new List<GachaHistoryDetailCellViewModel>());
    }
}