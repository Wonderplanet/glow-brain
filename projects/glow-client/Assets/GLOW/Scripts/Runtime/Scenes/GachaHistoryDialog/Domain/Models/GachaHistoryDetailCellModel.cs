using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.GachaHistoryDialog.Domain.Models
{
    public record GachaHistoryDetailCellModel(
        SortOrder SortOrder, 
        PlayerResourceModel CellIconPlayerResourceModel,
        CharacterName CharacterName,
        PlayerResourceModel AcquiredAmountPlayerResourceModel);
}