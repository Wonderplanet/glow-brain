using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record UserHomeMainKomaPatternModel(
        MasterDataId MstKomaPatternId,
        IReadOnlyList<UserHomeKomaUnitSettingModel> UserHomeKomaUnitSettingModels)
    {
        public static UserHomeMainKomaPatternModel CreateEmpty(MasterDataId mstKomaPatternId)
        {
            return new UserHomeMainKomaPatternModel(
                mstKomaPatternId,
                new List<UserHomeKomaUnitSettingModel>()
            );
        }
    };
}
