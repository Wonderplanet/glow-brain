using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record BeginnerMissionPromptPhrase(ObscuredString Value)
    {
        public static BeginnerMissionPromptPhrase Empty { get; } = new(string.Empty);
    }
}