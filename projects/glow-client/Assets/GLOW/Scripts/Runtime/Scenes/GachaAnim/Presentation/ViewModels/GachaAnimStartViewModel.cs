using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.GachaAnim.Presentation.ViewModels
{
    public record GachaAnimStartViewModel(Rarity StartRarity, Rarity EndRarity, int Count, List<GachaAnimIconInfo> IconInfos);
}
