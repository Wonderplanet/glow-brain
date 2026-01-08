using GLOW.Core.Domain.Models;
using GLOW.Scenes.GachaCostItemDetailView.Domain.ValueObject;

namespace GLOW.Scenes.GachaCostItemDetailView.Domain.Models
{
    public record GachaCostItemDetailUseCaseModel(
        PlayerResourceModel PlayerResourceModel,
        TransitionButtonGrayOutFlag TransitionButtonGrayOutFlag);
}