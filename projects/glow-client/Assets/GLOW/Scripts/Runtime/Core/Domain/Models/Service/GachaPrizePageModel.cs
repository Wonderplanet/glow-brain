using System.Collections.Generic;
using GLOW.Core.Domain.Models.Gacha;

namespace GLOW.Core.Domain.Models
{
    public record GachaPrizePageModel(
        IReadOnlyList<GachaRarityProbabilityModel> RarityProbabilities, //レアリティ別排出確率
        IReadOnlyList<GachaProbabilityGroupModel> ProbabilityGroupModels//レアリティ別排出内容
    )
    {
        public static GachaPrizePageModel Empty { get; } = new (
            new List<GachaRarityProbabilityModel>(),
            new List<GachaProbabilityGroupModel>()
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
