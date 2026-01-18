using System.Collections.Generic;

namespace GLOW.Scenes.GameModeSelect.Domain
{
    public record GameModeSelectUseCaseModel(IReadOnlyList<GameModeSelectUseCaseItemModel> Items);
}
