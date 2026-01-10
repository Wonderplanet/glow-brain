using System;

namespace GLOW.Debugs.Command.Domains.UseCase
{
    public record DebugTopUseCaseModel(DateTimeOffset CurrentTime, DebugCommandEnvName EnvName);
}
