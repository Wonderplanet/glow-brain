using System.Collections.Generic;

namespace GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.ViewModels
{
    public record EnhanceQuestScoreDetailViewModel(IReadOnlyList<EnhanceQuestScoreDetailCellViewModel> Cells);
}
