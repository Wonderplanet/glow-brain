using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaContent.Domain.Model;

namespace GLOW.Scenes.GachaList.Domain.Model
{
    public record GachaContentAssetUseCaseModel(GachaContentAssetPath GachaContentAssetPath)
    {
        public static GachaContentAssetUseCaseModel Empty { get; } = new(GachaContentAssetPath.Empty);
    };
}
