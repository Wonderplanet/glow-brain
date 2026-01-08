using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Modules.Log;

namespace WPFramework.Modules.TimeCalibration
{
    public sealed class DeviceTimeCalibrator : ITimeCalibrator
    {
        UniTask<long> ITimeCalibrator.Fetch(CancellationToken cancellationToken)
        {
            ApplicationLog.Log(nameof(DeviceTimeCalibrator), "端末時間を利用した校正を行います。");
            return UniTask.FromResult(DateTimeOffset.Now.ToUnixTimeMilliseconds());
        }
    }
}
