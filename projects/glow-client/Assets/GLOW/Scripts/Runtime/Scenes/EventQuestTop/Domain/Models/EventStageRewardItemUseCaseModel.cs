using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.EventStageSelect.Domain.Models
{
    public record EventStageRewardItemUseCaseModel(
        bool IsFirstOnly,
        bool IsGotten,
        ItemModel ItemModel
    );
}