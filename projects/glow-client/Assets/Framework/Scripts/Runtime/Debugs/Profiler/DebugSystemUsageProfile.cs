using System;
using UnityEngine;
using WonderPlanet.PerformanceProfiler;
using WonderPlanet.PerformanceProfiler.Evaluate;
using WonderPlanet.PerformanceProfiler.Utils;
using WPFramework.Modules.Log;

namespace WPFramework.Debugs.Profiler
{
    public class DebugSystemUsageProfile : MonoBehaviour
    {
        [Header("FPS")]
        [SerializeField] DebugSystemUsageComponent _fps;
        [SerializeField] FloatEvaluateRange[] _fpsEvaluateRanges;

        [Header("Memory")]
        [SerializeField] DebugSystemUsageComponent _usageMemorySize;
        [SerializeField] LongEvaluateRange[] _usageMemoryEvaluateRanges;

        [Header("Batch")]
        [SerializeField] DebugSystemUsageComponent _batchCount;
        [SerializeField] LongEvaluateRange[] _batchEvaluateRanges;

        [Header("Pass")]
        [SerializeField] DebugSystemUsageComponent _passCount;
        [SerializeField] LongEvaluateRange[] _passCallEvaluateRanges;

        [Header("CPU")]
        [SerializeField] DebugSystemUsageComponent _cpuFrameTime;

        [Header("GPU")]
        [SerializeField] DebugSystemUsageComponent _gpuFrameTime;

        public bool Enabled
        {
            get => gameObject.activeSelf;
            set => gameObject.SetActive(value);
        }

        void Awake()
        {
            // NOTE: 名前を変えてわかりやすくしておく
            name = $"[Debug] {nameof(DebugSystemUsageProfile)}";
        }

        void OnEnable()
        {
            ApplicationLog.Log(nameof(DebugSystemUsageProfile), "OnEnable");

            // NOTE: プロファイラーの設定を行う
            //       プロファイリングを一度止める場合はGameObjectのアクティブを切る
            FPSProfiler.SetEvaluator(new DefaultFPSEvaluator(_fpsEvaluateRanges));
            MemoryProfiler.SetEvaluator(new DefaultMemoryEvaluator(_usageMemoryEvaluateRanges));
            RendererProfiler.SetEvaluator(
                new DefaultRendererEvaluator(
                    drawCallRanges: Array.Empty<LongEvaluateRange>(),
                    setPassCallRanges: _passCallEvaluateRanges,
                    verticesRanges: Array.Empty<LongEvaluateRange>(),
                    trianglesRanges: Array.Empty<LongEvaluateRange>(),
                    batchesRanges: _batchEvaluateRanges));
        }

        void OnDisable()
        {
            ApplicationLog.Log(nameof(DebugSystemUsageProfile), "OnDisable");

            // NOTE: プロファイラーのリリースを行う
            FPSProfiler.Release();
            MemoryProfiler.Release();
            RendererProfiler.Release();
            CPUProfiler.Release();
            GPUProfiler.Release();
        }

        void Update()
        {
            var fps = FPSProfiler.GetFPS();
            _fps.SetText($"{fps.Value:N2}");
            _fps.SetMetricStatus(fps.Status);

            var usageMemorySize = MemoryProfiler.GetUsageMemorySize();
            var sizeToString = DataSizeConverter.ConvertToString((ulong)usageMemorySize.Value, DataSizeUnits.Megabyte);
            _usageMemorySize.SetText($"{sizeToString}");
            _usageMemorySize.SetMetricStatus(usageMemorySize.Status);

            var batchCount = RendererProfiler.GetBatchesCount();
            _batchCount.SetText($"B : {batchCount.Value}");
            _batchCount.SetMetricStatus(batchCount.Status);

            var passCount = RendererProfiler.GetSetPassCallCount();
            _passCount.SetText($"P : {passCount.Value}");
            _passCount.SetMetricStatus(passCount.Status);

            var cpuFrameTime = CPUProfiler.GetFrameTime();
            _cpuFrameTime.SetText($"CPU {cpuFrameTime.Value:N2} ms");
            _cpuFrameTime.SetMetricStatus(cpuFrameTime.Status);

            var gpuFrameTime = GPUProfiler.GetFrameTime();
            _gpuFrameTime.SetText($"GPU {gpuFrameTime.Value:N2} ms");
            _gpuFrameTime.SetMetricStatus(gpuFrameTime.Status);
        }

        void OnDestroy()
        {
            FPSProfiler.Release();
            MemoryProfiler.Release();
            RendererProfiler.Release();
            CPUProfiler.Release();
            GPUProfiler.Release();
        }
    }
}
