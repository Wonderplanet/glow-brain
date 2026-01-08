using GLOW.Scenes.Login.Domain.Constants.Login;

namespace GLOW.Scenes.Login.Domain.UseCase
{
    public record LoginPhaseUseCaseModel(LoginPhases LoginPhase)
    {
        public LoginPhases LoginPhase { get; } = LoginPhase;
    }
}
