using GLOW.Scenes.GachaRatio.Domain.Model;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.GachaLineupDialog.Domain.Models
{
    public record GachaLineupCellModel(
        GachaRatioResourceModel ResourceModel,
        PlayerResourceModel PlayerResourceModel,
        CharacterName CharacterName,
        PlayerResourceName ResourceName,
        NumberParity NumberParity
    )
    {
        public static GachaLineupCellModel Empty { get; } = new(
            GachaRatioResourceModel.Empty,
            PlayerResourceModel.Empty,
            CharacterName.Empty,
            PlayerResourceName.Empty,
            NumberParity.Empty
        );
    };
}