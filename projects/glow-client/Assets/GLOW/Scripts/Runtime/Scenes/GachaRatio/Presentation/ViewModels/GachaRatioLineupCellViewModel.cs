using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.GachaRatio.Domain.Model;

namespace GLOW.Scenes.GachaRatio.Presentation.ViewModels
{
    public record GachaRatioLineupCellViewModel(
        GachaRatioResourceModel ResourceModel,
        PlayerResourceIconViewModel PlayerResourceIconViewModel,
        CharacterName CharacterName,
        PlayerResourceName ResourceName,
        OutputRatio OutputRatio,
        NumberParity NumberParity,
        Action<GachaRatioResourceModel> ClickIconEvent
        );
}
