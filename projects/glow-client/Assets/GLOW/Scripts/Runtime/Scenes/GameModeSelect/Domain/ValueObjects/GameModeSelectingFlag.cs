namespace GLOW.Scenes.GameModeSelect.Domain
{
    public record GameModeSelectingFlag(bool Value)
    {
        public static GameModeSelectingFlag True { get; } = new GameModeSelectingFlag(true);
        public static GameModeSelectingFlag False { get; } = new GameModeSelectingFlag(false);
    };
}