using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine;
using WonderPlanet.DebugReporter.Log;
using WonderPlanet.DebugReporter.Report;
using WonderPlanet.DebugReporter.Reporter;
using WonderPlanet.DebugReporter.ScreenShot;
using WonderPlanet.DebugReporter.Token;
using WPFramework.Modules.Log;
using GLOW.Debugs.Constants;
using Zenject;

namespace GLOW.Debugs.Reporter
{
    public sealed class DebugReporter : IReporter, IInitializable
    {
        ITokenClient _tokenClient;
        ScreenShotClient _screenShotClient;
        readonly List<ILogReport> _reports = new();
        ILogReporter _logReporter;
        bool _isDisposed;
        bool _isInitialized;

        CancellationTokenSource _cancellationTokenSource;

        void IInitializable.Initialize()
        {
            _cancellationTokenSource = new CancellationTokenSource();
            _tokenClient = new StreamingAssetTokenClient();
            _screenShotClient = new ScreenShotClient();
            _logReporter = new SlackLogReporter(_tokenClient, ReportSettings.ChannelId, new MemoryReporterLogHandler(999));

            _screenShotClient.Delete();

            ApplicationLog.Log(nameof(DebugReporter), "DebugReporter is initialized.");

            _isInitialized = true;
        }

        async UniTask IReporter.Send(CancellationToken cancellationToken)
        {
            using var cts = CancellationTokenSource.CreateLinkedTokenSource(_cancellationTokenSource.Token, cancellationToken);

            try
            {
                if (!_isInitialized)
                {
                    throw new InvalidOperationException($"{nameof(DebugReporter)} is not initialized.");
                }

                if (_isDisposed)
                {
                    throw new ObjectDisposedException(nameof(DebugReporter));
                }

                string reportID;
                // NOTE: 事前に撮影したデータがあれば利用する
                if (_screenShotClient.ExistLast())
                {
                    reportID = await _logReporter.Report(cts.Token, _screenShotClient.LoadLast(), _reports.ToArray());
                }
                else
                {
                    reportID = await _logReporter.Report(cts.Token, _reports.ToArray());
                }

                // NOTE: クリップボードにレポートIDをコピーする
                GUIUtility.systemCopyBuffer = reportID;
            }
            catch (Exception e)
            {
                ApplicationLog.LogWarning(nameof(DebugReporter), e.ToString());
            }
            finally
            {
                _screenShotClient?.Delete();
            }
        }

        void IReporter.ClearReport()
        {
            foreach (var report in _reports)
            {
                report?.Dispose();
            }

            _reports.Clear();
        }

        void IReporter.AddReport(ILogReport report)
        {
            _reports.Add(report);
        }

        async UniTask IReporter.Capture(CancellationToken cancellationToken)
        {
            using var cts = CancellationTokenSource.CreateLinkedTokenSource(_cancellationTokenSource.Token, cancellationToken);

            try
            {
                if (!_isInitialized)
                {
                    throw new InvalidOperationException($"{nameof(DebugReporter)} is not initialized.");
                }

                if (_isDisposed)
                {
                    throw new ObjectDisposedException(nameof(DebugReporter));
                }

                // NOTE: 複数回撮影されないようにチェック
                var captureCount = _screenShotClient.CaptureCount;
                if (captureCount > 0)
                {
                    return;
                }

                await _screenShotClient.CaptureAndSave(cts.Token);
            }
            catch (Exception e)
            {
                ApplicationLog.LogWarning(nameof(DebugReporter), e.ToString());
            }
        }

        async UniTask IReporter.WaitForCapture(CancellationToken cancellationToken)
        {
            using var cts = CancellationTokenSource.CreateLinkedTokenSource(_cancellationTokenSource.Token, cancellationToken);

            ScreenShotClient.CaptureRunner runner = null;

            try
            {
                if (!_isInitialized)
                {
                    throw new InvalidOperationException($"{nameof(DebugReporter)} is not initialized.");
                }

                if (_isDisposed)
                {
                    throw new ObjectDisposedException(nameof(DebugReporter));
                }

                if (_screenShotClient.CaptureCount == 0)
                {
                    return;
                }

#if UNITY_EDITOR
                // NOTE: エディタだと処理が早すぎるので待ち時間を作る（シミュレーション）
                await UniTask.Delay(TimeSpan.FromSeconds(0.5), cancellationToken: cancellationToken);
#endif // UNITY_EDITOR

                var latencySeconds = 0.0f;
                var latencySecondsLimit = 20.0f;
                var isTimeout = true;
                runner = new GameObject("ScreenShotClient.Runner").AddComponent<ScreenShotClient.CaptureRunner>();
                while (latencySeconds < latencySecondsLimit)
                {
                    if (_screenShotClient.ExistLast())
                    {
                        isTimeout = false;
                        break;
                    }

                    latencySeconds += Time.deltaTime;

                    await UniTask.WaitForEndOfFrame(runner, cts.Token);
                }

                if (isTimeout)
                {
                    ApplicationLog.LogWarning(nameof(DebugReporter), "ScreenShotClient.WaitForCapture is timeout.");
                }
            }
            catch (Exception e)
            {
                ApplicationLog.LogWarning(nameof(DebugReporter), e.ToString());
            }
            finally
            {
                if (runner && runner.gameObject)
                {
                    UnityEngine.Object.Destroy(runner.gameObject);
                }
            }
        }

        public void Dispose()
        {
            if (_isDisposed)
            {
                return;
            }

            _isDisposed = true;

            foreach (var report in _reports)
            {
                report?.Dispose();
            }
            _reports.Clear();

            _screenShotClient?.Delete();
            _logReporter.Dispose();

            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
        }
    }
}
