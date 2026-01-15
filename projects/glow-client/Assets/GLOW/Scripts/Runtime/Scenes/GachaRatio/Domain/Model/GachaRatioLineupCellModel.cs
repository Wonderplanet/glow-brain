using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.GachaRatio.Domain.Model
{
    public record GachaRatioLineupCellModel(
        GachaRatioResourceModel ResourceModel,
        PlayerResourceModel PlayerResourceModel,
        CharacterName CharacterName,
        PlayerResourceName ResourceName,
        OutputRatio OutputRatio,
        NumberParity NumberParity
    )
    {
        public static GachaRatioLineupCellModel Empty { get; } = new(
            GachaRatioResourceModel.Empty,
            PlayerResourceModel.Empty,
            CharacterName.Empty,
            PlayerResourceName.Empty,
            OutputRatio.Empty,
            NumberParity.Empty
        );
    };
}
