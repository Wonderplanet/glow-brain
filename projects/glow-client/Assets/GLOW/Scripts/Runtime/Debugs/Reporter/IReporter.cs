using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using WonderPlanet.DebugReporter.Report;

namespace GLOW.Debugs.Reporter
{
    public interface IReporter : IDisposable
    {
        void AddReport(ILogReport report);
        void ClearReport();
        UniTask Send(CancellationToken cancellationToken);
        UniTask Capture(CancellationToken cancellationToken);
        UniTask WaitForCapture(CancellationToken cancellationToken);
    }
}
