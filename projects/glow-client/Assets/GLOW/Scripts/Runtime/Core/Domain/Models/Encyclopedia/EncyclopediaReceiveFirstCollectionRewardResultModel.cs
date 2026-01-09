using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.Encyclopedia
{
    public record EncyclopediaReceiveFirstCollectionRewardResultModel(
        UserParameterModel UserParameter,
        IReadOnlyList<RewardModel> RewardModels,
        IReadOnlyList<UserEmblemModel> UserEmblems,
        IReadOnlyList<UserArtworkModel> UserArtworks,
        IReadOnlyList<UserEnemyDiscoverModel> UserEnemyDiscoveries,
        IReadOnlyList<UserUnitModel> UserUnits)
    {

    }
}
