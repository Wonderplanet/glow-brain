using System;
using System.IO;
using System.Text;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine;
using WonderPlanet.StorageSupporter;

namespace WPFramework.Modules.Environment
{
    public sealed class StreamingAssetEnvironmentProtocol : IEnvironmentProtocol
    {
        public record Settings(string EnvironmentListFilePath)
        {
            public string EnvironmentListFilePath { get; } = EnvironmentListFilePath;
        }

        int IEnvironmentProtocol.Priority => 222;

        readonly CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();
        readonly IEnvironmentDataParser _environmentDataParser;
        readonly Settings _settings;

        bool _isDisposed;

        public StreamingAssetEnvironmentProtocol(Settings settings, IEnvironmentDataParser environmentDataParser)
        {
            _settings = settings;
            _environmentDataParser = environmentDataParser;
        }

        async UniTask<EnvironmentListData> IEnvironmentProtocol.FetchEnvironmentList(CancellationToken cancellationToken)
        {
            using var cts = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken, _cancellationTokenSource.Token);

            try
            {
                var path = Path.Combine(Application.streamingAssetsPath, _settings.EnvironmentListFilePath);
                var bin = await FileSupport.ReadAllBytesAsync(cts.Token, path);
                var text = Encoding.UTF8.GetString(bin);
                var environmentListData = _environmentDataParser.Parse<EnvironmentListData>(text);
                return environmentListData;
            }
            catch (Exception)
            {
                // NOTE: AndroidはUnityWebRequestでStreamingAssetを取りに行くので全体的にエラーをキャプチャしておく
                return await UniTask.FromResult(new EnvironmentListData(Array.Empty<EnvironmentData>()));
            }
        }

        public void Dispose()
        {
            if (_isDisposed)
            {
                return;
            }

            _isDisposed = true;

            _cancellationTokenSource.Cancel();
            _cancellationTokenSource.Dispose();
        }
    }
}
