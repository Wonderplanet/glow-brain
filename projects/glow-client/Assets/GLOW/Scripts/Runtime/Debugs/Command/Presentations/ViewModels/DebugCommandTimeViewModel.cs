using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Debugs.Command.Presentations.ViewModels
{
    public record DebugCommandTimeViewModel(DateTimeOffset CurrentTime)
    {
        public DateTimeOffset CurrentTime { get; } = CurrentTime;
    }
}
