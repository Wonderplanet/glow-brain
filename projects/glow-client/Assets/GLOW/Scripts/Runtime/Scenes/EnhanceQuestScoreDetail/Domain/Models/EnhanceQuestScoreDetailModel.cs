using System.Collections.Generic;

namespace GLOW.Scenes.EnhanceQuestScoreDetail.Domain.Models
{
    public record EnhanceQuestScoreDetailModel(IReadOnlyList<EnhanceQuestScoreDetailCellModel> Cells);
}
