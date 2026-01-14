

using GLOW.Scenes.Login.Domain.Constants.Login;

namespace GLOW.Scenes.Login.Domain.UseCase
{
    public record LoginPhaseLabel(LoginPhases LoginPhase)
    {
        public LoginPhases LoginPhase { get; } = LoginPhase;
    }
}
