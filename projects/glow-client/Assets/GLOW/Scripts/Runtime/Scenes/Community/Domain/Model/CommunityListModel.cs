using GLOW.Core.Domain.ValueObjects.Community;

namespace GLOW.Scenes.Community.Domain.Model
{
    public record CommunityListModel(
        CommunityUrl CommunityUrl, 
        CommunityUrlSchema CommunityUrlSchema);
}