using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaContent.Presentation.ViewModels;

namespace GLOW.Scenes.GachaList.Presentation.ViewModels
{
    public record GachaContentAssetViewModel(
        //アセットの中身に合わせてここの要素を可変させていく
        GachaContentAssetPath GachaContentAssetPath
    )
    {
        public static GachaContentAssetViewModel Empty { get; } = new(
            GachaContentAssetPath.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
