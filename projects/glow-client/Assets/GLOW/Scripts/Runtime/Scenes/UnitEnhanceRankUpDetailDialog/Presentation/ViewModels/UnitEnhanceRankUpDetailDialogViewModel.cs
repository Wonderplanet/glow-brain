using System.Collections.Generic;

namespace GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.ViewModels
{
    public record UnitEnhanceRankUpDetailDialogViewModel(
        IReadOnlyList<UnitEnhanceRankUpDetailCellViewModel> CellViewModelList);
}
