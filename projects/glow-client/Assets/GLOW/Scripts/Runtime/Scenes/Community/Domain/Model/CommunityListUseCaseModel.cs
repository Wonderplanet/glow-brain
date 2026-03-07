using GLOW.Core.Domain.ValueObjects.Community;
using GLOW.Scenes.Community.Domain.Model;

namespace GLOW.Scenes.Community.Domain.Model
{
}
    public record CommunityListUseCaseModel(
        CommunityListModel JumbleRushOfficialSiteCommunityListModel,
        CommunityListModel JumbleRushOfficialXCommunityListModel,
        CommunityListModel JumpPlusOfficialSiteCommunityListModel,
        CommunityListModel JumpPlusLinkCommunityListModel,
        CommunityListModel JumpPlusOfficialXCommunityListModel);