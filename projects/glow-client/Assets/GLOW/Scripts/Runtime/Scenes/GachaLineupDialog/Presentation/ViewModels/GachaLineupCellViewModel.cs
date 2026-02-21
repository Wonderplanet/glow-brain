using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.GachaRatio.Domain.Model;

namespace GLOW.Scenes.GachaLineupDialog.Presentation.ViewModels
{
    public record GachaLineupCellViewModel(
        GachaRatioResourceModel ResourceModel,
        PlayerResourceIconViewModel PlayerResourceIconViewModel,
        CharacterName CharacterName,
        PlayerResourceName ResourceName,
        NumberParity NumberParity,
        Action<GachaRatioResourceModel> ClickIconEvent
    );
}