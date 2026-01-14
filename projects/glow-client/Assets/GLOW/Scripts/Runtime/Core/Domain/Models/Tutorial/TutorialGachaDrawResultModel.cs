using System.Collections.Generic;
using GLOW.Core.Domain.Models.Gacha;

namespace GLOW.Core.Domain.Models.Tutorial
{
    public record TutorialGachaDrawResultModel(List<GachaResultModel> GachaResultModels)
    {
        public static TutorialGachaDrawResultModel Empty { get; } = new TutorialGachaDrawResultModel(new List<GachaResultModel>());
    }
}
