using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine;

namespace WPFramework.Modules.Environment
{
    public sealed class ResourcesEnvironmentProtocol : IEnvironmentProtocol
    {
        public record Settings(string EnvironmentListFilePath)
        {
            public string EnvironmentListFilePath { get; } = EnvironmentListFilePath;
        }

        int IEnvironmentProtocol.Priority => 200;

        readonly CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();
        readonly IEnvironmentDataParser _environmentDataParser;

        readonly Settings _settings;

        bool _isDisposed;

        public ResourcesEnvironmentProtocol(Settings settings, IEnvironmentDataParser environmentDataParser)
        {
            _settings = settings;
            _environmentDataParser = environmentDataParser;
        }

        async UniTask<EnvironmentListData> IEnvironmentProtocol.FetchEnvironmentList(CancellationToken cancellationToken)
        {
            using var cts = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken, _cancellationTokenSource.Token);
            try
            {
                var textAsset = await Resources.LoadAsync<TextAsset>(_settings.EnvironmentListFilePath).ToUniTask(cancellationToken: cts.Token) as TextAsset;
                if (textAsset == null)
                {
                    return await UniTask.FromResult(new EnvironmentListData(Array.Empty<EnvironmentData>()));
                }

                var text = textAsset.text;
                var environmentListData = _environmentDataParser.Parse<EnvironmentListData>(text);
                return environmentListData;
            }
            catch (Exception e)
            {
                Console.WriteLine(e);
                throw;
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
