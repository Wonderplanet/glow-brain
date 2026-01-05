using System;
using System.Collections.Generic;
using System.Text;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine.Localization.Settings;
using WPFramework.Modules.Log;

namespace WPFramework.Modules.Localization.Terms
{
    public sealed class LocalizationTermsManager : ILocalizationTermsManagement, ILocalizationTermsSource, IDisposable
    {
        ILocalizationAssetSource _assetSource;
        readonly Dictionary<string, string> _stringPrimitiveTable = new();
        bool _isInitialized;
        bool _isDisposed;

        readonly CancellationTokenSource _cancellationTokenSource = new();

        async UniTask ILocalizationTermsManagement.Initialize(CancellationToken cancellationToken, ILocalizationAssetSource assetSource)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(LocalizationTermsManager));
            }

            if (_isInitialized)
            {
                throw new InvalidOperationException($"{nameof(LocalizationTermsManager)} is already initialized.");
            }

            if (assetSource == null)
            {
                throw new ArgumentNullException(nameof(assetSource));
            }

            using var cts = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken, _cancellationTokenSource.Token);

            await LocalizationSettings.InitializationOperation.ToUniTask(cancellationToken: cts.Token);

            _assetSource = assetSource;
            _isInitialized = true;

            ApplicationLog.Log(nameof(LocalizationTermsManager), "Initialize");
        }

        async UniTask ILocalizationTermsManagement.Load(CancellationToken cancellationToken, string tableReference)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(LocalizationTermsManager));
            }

            if (!_isInitialized)
            {
                throw new InvalidOperationException($"{nameof(LocalizationTermsManager)} is not initialized.");
            }

            using var cts = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken, _cancellationTokenSource.Token);

            var stringTable = await _assetSource.GetStringTable(cts.Token, tableReference);

            foreach (var entry in stringTable.Values)
            {
                // NOTE: カテゴリ分けのために空白業が元データに挿入されていた場合にはスキップを行う
                if (string.IsNullOrEmpty(entry.Key))
                {
                    ApplicationLog.LogWarning(nameof(LocalizationTermsManager), "Empty key is detected.");
                    continue;
                }
                _stringPrimitiveTable[entry.Key] = entry.Value;
            }

            ApplicationLog.Log(nameof(LocalizationTermsManager), $"Load string primitive table. Count : {_stringPrimitiveTable.Count}");
        }

        void ILocalizationTermsManagement.Unload()
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(LocalizationTermsManager));
            }

            if (!_isInitialized)
            {
                throw new InvalidOperationException($"{nameof(LocalizationTermsManager)} is not initialized.");
            }

            _stringPrimitiveTable.Clear();
        }

        void ILocalizationTermsManagement.Dump()
        {
            var builder = new StringBuilder();

            builder.AppendLine("Dump string primitive table.");
            builder.AppendLine($"Count : {_stringPrimitiveTable.Count}");
            if (_stringPrimitiveTable.Count == 0)
            {
                builder.AppendLine("--------------------");
                builder.AppendLine("No data.");
            }
            else
            {
                builder.AppendLine("--------------------");
                foreach (var data in _stringPrimitiveTable)
                {
                    builder.AppendLine($"{data.Key} : {data.Value}");
                }
            }

            ApplicationLog.Log(nameof(LocalizationTermsManager), builder.ToString());
        }

        string ILocalizationTermsSource.Get(string key)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(LocalizationTermsManager));
            }

            if (!_isInitialized)
            {
                throw new InvalidOperationException($"{nameof(LocalizationTermsManager)} is not initialized.");
            }

            return !_stringPrimitiveTable.TryGetValue(key, out var value) ? key : value;
        }

        string ILocalizationTermsSource.Get(string key, params object[] args)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(LocalizationTermsManager));
            }

            if (!_isInitialized)
            {
                throw new InvalidOperationException($"{nameof(LocalizationTermsManager)} is not initialized.");
            }

            return !_stringPrimitiveTable.TryGetValue(key, out var value) ? key : string.Format(value, args);
        }

        void IDisposable.Dispose()
        {
            if (_isDisposed)
            {
                return;
            }

            _isDisposed = true;

            _stringPrimitiveTable.Clear();
            _assetSource = null;

            _cancellationTokenSource.Cancel();
            _cancellationTokenSource.Dispose();
        }
    }
}
