using System.Threading;
using Cysharp.Threading.Tasks;

namespace WPFramework.Modules.TimeCalibration
{
    public interface ITimeCalibrator
    {
        UniTask<long> Fetch(CancellationToken cancellationToken);
    }
}
