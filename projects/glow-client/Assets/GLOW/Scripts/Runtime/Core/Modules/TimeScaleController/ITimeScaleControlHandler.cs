using System;

namespace GLOW.Core.Modules.TimeScaleController
{
    public interface ITimeScaleControlHandler : IDisposable
    {
        float TimeScale { get; }
    }
}