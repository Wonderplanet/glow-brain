using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.Title.Domains.UseCase
{
    public record StageSessionOpenFlag(ObscuredBool Value)
    {
        public static StageSessionOpenFlag True { get; } = new(true);
        public static StageSessionOpenFlag False { get; } = new(false);
        public static implicit operator bool(StageSessionOpenFlag value) => value.Value;
    };
}