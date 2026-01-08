using System;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine;
using UnityEngine.Localization.Settings;
using UnityEngine.Localization.Tables;
using WPFramework.Modules.Log;

namespace WPFramework.Modules.Localization
{
    public sealed class LocalizationAssetManager : ILocalizationAssetManagement, ILocalizationLocaleSelector, ILocalizationAssetSource, ILocalizationInformationProvider, IDisposable
    {
        bool _isInitialized;
        bool _isDisposed;
        const string PrefsKey = "WPFramework.Modules.Localization.LocalizationAssetManager.NewLocaleKey";

        string ILocalizationInformationProvider.LocaleCode =>
            !LocalizationSettings.SelectedLocale ?
                "Unknown Locale Code" : LocalizationSettings.SelectedLocale.Identifier.Code;

        readonly CancellationTokenSource _cancellationTokenSource = new();

        async UniTask ILocalizationAssetManagement.Initialize(CancellationToken cancellationToken)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(LocalizationAssetManager));
            }

            if (_isInitialized)
            {
                ResetStateIfHasSettings();

                // NOTE: 再ロードになるために一度フラグを落としておく
                _isInitialized = false;
            }

            // NOTE: Addressablesの初期化が同一フレームで行われた場合に１フレームまたないとエラーが出る場合があるため待機
            using var cts = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken, _cancellationTokenSource.Token);

            await UniTask.Yield(cts.Token);

            // NOTE: Addressablesの初期化を含めて待機する
            //       Addressablesの初期化を行っていない状態でここへ到達するとAddressablesの初期化処理を実行する
            await LocalizationSettings.InitializationOperation.ToUniTask(cancellationToken: cts.Token);

            // NOTE: 言語変更の予約がある場合、設定言語を更新する
            //       また言語更新が行われた際に初期化処理が発生するため内部て初期化待ちが実行される
            await ApplyReservedLocale(cts.Token);

            _isInitialized = true;

            ApplicationLog.Log(nameof(LocalizationAssetManager),
                $"Initialize Selected Locale Identifier {LocalizationSettings.SelectedLocale.Identifier.Code}");
        }

        async UniTask ApplyReservedLocale(CancellationToken cancellationToken)
        {
            // NOTE: 初回の言語変更を行うまでは空文字となっている
            var newLocaleType = PlayerPrefs.GetString(PrefsKey, "");
            if (string.IsNullOrEmpty(newLocaleType))
            {
                return;
            }

            var currentLocaleType = LocalizationSettings.SelectedLocale.Identifier.Code;
            if (newLocaleType == currentLocaleType)
            {
                return;
            }

            var newLocale = LocalizationSettings.AvailableLocales.GetLocale(newLocaleType);
            if (!newLocale)
            {
                // NOTE: 存在しない言語を指定しているためPlayerPrefsをリセットする
                PlayerPrefs.SetString(PrefsKey, "");
                return;
            }

            ApplicationLog.Log(nameof(LocalizationAssetManager), $"Apply Reserved Locale {newLocale.Identifier.Code}");

            ResetStateIfHasSettings();

            // NOTE: ロケールを入れ替える
            LocalizationSettings.SelectedLocale = newLocale;

            // NOTE: Addressablesの初期化を含めて待機する
            //       Addressablesの初期化を行っていない状態でここへ到達するとAddressablesの初期化処理を実行する
            await LocalizationSettings.InitializationOperation.ToUniTask(cancellationToken: cancellationToken);
        }

        async UniTask ILocalizationAssetManagement.PreloadAssetDatabase(CancellationToken cancellationToken, string tableReference)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(LocalizationAssetManager));
            }

            if (!_isInitialized)
            {
                throw new InvalidOperationException($"{nameof(LocalizationAssetManager)} is not initialized.");
            }

            using var cts = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken, _cancellationTokenSource.Token);

            await LocalizationSettings.AssetDatabase.PreloadTables(tableReference).ToUniTask(cancellationToken: cts.Token);
        }

        async UniTask ILocalizationAssetManagement.PreloadStringDatabase(CancellationToken cancellationToken, string tableReference)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(LocalizationAssetManager));
            }

            if (!_isInitialized)
            {
                throw new InvalidOperationException($"{nameof(LocalizationAssetManager)} is not initialized.");
            }

            using var cts = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken, _cancellationTokenSource.Token);

            await LocalizationSettings.StringDatabase.PreloadTables(tableReference).ToUniTask(cancellationToken: cts.Token);
        }

        void ILocalizationAssetManagement.ReleaseStringTable(string tableReference)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(LocalizationAssetManager));
            }

            if (!_isInitialized)
            {
                throw new InvalidOperationException($"{nameof(LocalizationAssetManager)} is not initialized.");
            }

            // NOTE: テーブルエントリへの参照がある場合解放されないので注意
            LocalizationSettings.StringDatabase.ReleaseTable(tableReference);
        }

        void ILocalizationAssetManagement.ReleaseAssetTable(string tableReference)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(LocalizationAssetManager));
            }

            if (!_isInitialized)
            {
                throw new InvalidOperationException($"{nameof(LocalizationAssetManager)} is not initialized.");
            }

            // NOTE: テーブルエントリへの参照がある場合解放されないので注意
            LocalizationSettings.AssetDatabase.ReleaseTable(tableReference);
        }

        async UniTask ILocalizationLocaleSelector.ChangeLocale(CancellationToken cancellationToken, string newLocaleName)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(LocalizationAssetManager));
            }

            if (!_isInitialized)
            {
                throw new InvalidOperationException($"{nameof(LocalizationAssetManager)} is not initialized.");
            }

            using var cts = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken, _cancellationTokenSource.Token);

            var newLocale = LocalizationSettings.AvailableLocales.GetLocale(newLocaleName);
            if (!newLocale)
            {
                throw new ArgumentException($"Locale {newLocaleName} is not available.");
            }

            // NOTE: ロケールを入れ替える
            LocalizationSettings.SelectedLocale = newLocale;

            // NOTE: 初期化が完了するまで待機する
            await LocalizationSettings.InitializationOperation.ToUniTask(cancellationToken: cts.Token);

            ApplicationLog.Log(nameof(LocalizationAssetManager), $"Change Locale {newLocale.Identifier.Code}");
        }

        string[] ILocalizationLocaleSelector.GetAvailableLocaleNames()
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(LocalizationAssetManager));
            }

            if (!_isInitialized)
            {
                throw new InvalidOperationException($"{nameof(LocalizationAssetManager)} is not initialized.");
            }

            return LocalizationSettings.AvailableLocales.Locales
                .Select(locale => locale.Identifier.Code)
                .ToArray();
        }

        void ILocalizationLocaleSelector.ReserveNewLocale(string newLocaleName)
        {
            PlayerPrefs.SetString(PrefsKey, newLocaleName);
        }

        async UniTask<StringTable> ILocalizationAssetSource.GetStringTable(CancellationToken cancellationToken, string tableReference)
        {
            using var cts = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken, _cancellationTokenSource.Token);

            var table = await LocalizationSettings.StringDatabase.GetTableAsync(tableReference).Task;
            return table;
        }

        void ResetStateIfHasSettings()
        {
            if (!LocalizationSettings.HasSettings)
            {
                return;
            }

            // NOTE: 確保しているテーブルをすべて解放する
            LocalizationSettings.Instance.ResetState();

            ApplicationLog.Log(nameof(LocalizationAssetManager), "Soft Reset State");
        }

        void IDisposable.Dispose()
        {
            if (_isDisposed)
            {
                return;
            }

            _isDisposed = true;

            LocalizationSettings.Instance.ResetState();
            // NOTE: LocalizationSettings.InstanceのDisposeがないため明示的にDisposeを呼ぶ
            ((IDisposable)LocalizationSettings.Instance).Dispose();
            LocalizationSettings.Instance = null;

            _cancellationTokenSource.Cancel();
            _cancellationTokenSource.Dispose();;
        }
    }
}
