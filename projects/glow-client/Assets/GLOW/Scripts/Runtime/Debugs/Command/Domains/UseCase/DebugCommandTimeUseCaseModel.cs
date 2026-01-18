using System;

namespace GLOW.Debugs.Command.Domains.UseCase
{
    public record DebugCommandTimeUseCaseModel(DateTimeOffset CurrentTime)
    {
        public DateTimeOffset CurrentTime { get; } = CurrentTime;
    }
}
