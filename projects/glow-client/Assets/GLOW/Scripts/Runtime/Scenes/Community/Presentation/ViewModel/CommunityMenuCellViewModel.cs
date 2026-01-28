using GLOW.Core.Domain.ValueObjects.Community;

namespace GLOW.Scenes.Community.Presentation.ViewModel
{
    public record CommunityMenuCellViewModel(
        CommunityUrl Url,
        CommunityUrlSchema Schema);
}