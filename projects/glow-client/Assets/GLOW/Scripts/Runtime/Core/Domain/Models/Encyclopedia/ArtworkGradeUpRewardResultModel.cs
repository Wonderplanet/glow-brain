using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.Encyclopedia
{
    public record ArtworkGradeUpRewardResultModel(
        UserArtworkModel UserArtwork,
        IReadOnlyList<UserItemModel> UserItems);
}
