namespace GLOW.Scenes.QuestContentTop.Domain.ValueObject
{
    public record ArtworkPanelMissionExistFlag(bool Value)
    {
        public static ArtworkPanelMissionExistFlag True { get; } = new(true);
        public static ArtworkPanelMissionExistFlag False { get; } = new(false);
        
        public static implicit operator bool(ArtworkPanelMissionExistFlag flag) => flag.Value;
    }
}