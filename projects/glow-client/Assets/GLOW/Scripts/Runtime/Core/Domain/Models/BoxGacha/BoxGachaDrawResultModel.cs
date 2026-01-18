using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.BoxGacha
{
    public record BoxGachaDrawResultModel(
        UserParameterModel UserParameterModel,
        IReadOnlyList<UserItemModel> UserItemModels,
        IReadOnlyList<UserUnitModel> UserUnitModels,
        IReadOnlyList<UserArtworkModel> UserArtworkModels,
        IReadOnlyList<UserArtworkFragmentModel> UserArtworkFragmentModels,
        UserBoxGachaModel UserBoxGachaModel,
        IReadOnlyList<BoxGachaRewardModel> BoxGachaRewardModels)
    {
        public static BoxGachaDrawResultModel Empty { get; } = new(
            UserParameterModel.Empty,
            new List<UserItemModel>(),
            new List<UserUnitModel>(),
            new List<UserArtworkModel>(),
            new List<UserArtworkFragmentModel>(),
            UserBoxGachaModel.Empty,
            new List<BoxGachaRewardModel>()
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}