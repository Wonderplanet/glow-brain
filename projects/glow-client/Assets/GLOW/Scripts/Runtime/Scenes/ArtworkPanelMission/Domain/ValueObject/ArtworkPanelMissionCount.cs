using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.ValueObject
{
    public record ArtworkPanelMissionCount(ObscuredInt Value)
    {
        public static ArtworkPanelMissionCount Empty { get; } = new(0);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}