using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.Title.Domains.UseCase
{
    public record SessionAbortConfirmAttentionText(ObscuredString Value)
    {
        public static SessionAbortConfirmAttentionText Empty { get; } = new(string.Empty);
    };
}