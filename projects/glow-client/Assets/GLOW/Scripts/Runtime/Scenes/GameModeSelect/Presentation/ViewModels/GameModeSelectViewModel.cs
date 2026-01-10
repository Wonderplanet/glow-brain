using System.Collections.Generic;

namespace GLOW.Scenes.GameModeSelect.Presentation
{
    public record GameModeSelectViewModel(IReadOnlyList<GameModeSelectItemViewModel> Items)
    {
        public static GameModeSelectViewModel Empty { get; } = new GameModeSelectViewModel(new List<GameModeSelectItemViewModel>());
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };
}


