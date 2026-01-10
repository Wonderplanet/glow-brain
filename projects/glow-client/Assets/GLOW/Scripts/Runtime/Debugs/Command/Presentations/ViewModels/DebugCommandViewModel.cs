using GLOW.Debugs.Command.Domains.UseCase;

namespace GLOW.Debugs.Command.Presentations.ViewModels
{
    public record DebugCommandViewModel(DebugCommandTimeViewModel TimeViewModel, DebugCommandEnvName EnvName);
}
