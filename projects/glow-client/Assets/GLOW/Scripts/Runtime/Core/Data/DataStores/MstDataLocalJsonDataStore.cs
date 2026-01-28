using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.Data;
using GLOW.Core.Data.DataStores.Cache;
using GLOW.Core.Data.DataStores.Decryptor;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.MasterData;
using GLOW.Core.Domain.Modules.Serializers;
using GLOW.Core.Exceptions;
using Newtonsoft.Json;
using WPFramework.Constants.MasterData;
using WPFramework.Modules.Log;
using WonderPlanet.StorageSupporter;

namespace GLOW.Core.Data.DataStores
{
    public sealed class MstDataLocalJsonDataStore : IMstDataDataStore, IDisposable
    {
        readonly Dictionary<Type, MstCacheData> _mstDataCacheTable = new();
        readonly bool _enableDecryption;

        bool _isDisposed;

        public MstDataLocalJsonDataStore(bool enableDecryption)
        {
            _enableDecryption = enableDecryption;
        }

        async UniTask IMstDataDataStore.Load(
            CancellationToken cancellationToken,
            MasterType masterType,
            string name,
            string hash,
            Language language)
        {
            // NOTE: 暗号化自体はクライアントでは施さないためそのまま保存する。
            //       また、暗号化を実装する際にはバイトデータから文字列を生成する際に変換処理を噛ませるようにする
            //       ファイル名でTableキャッシュに入れる値を変える場合はnameから処理を引く
            var path = MstDataPath.GetLocalFilePath(name, masterType);
            var data = await FileSupport.ReadAllBytesAsync(cancellationToken, path);

            ApplicationLog.Log(
                nameof(MstDataLocalJsonDataStore),
                ZString.Format("Load: [{0}] data size: [{1:N0} bytes]", path, data.Length));

            if (_enableDecryption)
            {
                try
                {
                    IMasterDataDecryptor decryptor = new MasterDataDecryptor(hash);
                    data = Decrypt(data, decryptor);
                }
                catch (Exception e)
                {
                    throw new MasterDataDecryptException(path, masterType, e);
                }
            }

            // NOTE: キャッシュデータの特定のタイプを削除する
            RemoveCacheTable(masterType);

            // NOTE: キャッシュデータを生成する
            var cacheTable = GenerateCacheTable(masterType, language, data);
            AddCacheTable(cacheTable);
        }

        void IMstDataDataStore.Save(string name, MasterType masterType, byte[] data)
        {
            var path = MstDataPath.GetLocalFilePath(name, masterType);

            ApplicationLog.Log(
                nameof(MstDataLocalJsonDataStore),
                ZString.Format("Save: [{0}] data size: [{1:N0} bytes]", path, data.Length));

            FileSupport.WriteAllBytesWithoutBackup(path, data);
        }

        void IMstDataDataStore.DeleteAll(MasterType masterType)
        {
            // NOTE:「mst_data/」以下に存在する古いマスターデータを削除する
            var existingMstDataDirPath = MstDataPath.GetLocalDirectoryPath(masterType);
            if (!Directory.Exists(existingMstDataDirPath))
            {
                return;
            }

            var files = Directory.GetFiles(existingMstDataDirPath);
            foreach (var file in files)
            {
                File.Delete(file);
            }
        }

        bool IMstDataDataStore.Validate(MasterType masterType, string name)
        {
            var path = MstDataPath.GetLocalFilePath(name, masterType);
            return File.Exists(path);
        }

        IEnumerable<T> IMstDataDataStore.Get<T>()
        {
            if (_mstDataCacheTable.TryGetValue(typeof(T), out var data))
            {
                return data.Data as IEnumerable<T>;
            }

            ApplicationLog.LogWarning(
                nameof(MstDataLocalJsonDataStore),
                ZString.Format("MstData not found. type: {0}", typeof(T)));

            foreach(var cache in _mstDataCacheTable)
            {
                ApplicationLog.LogWarning(
                    nameof(MstDataLocalJsonDataStore),
                    ZString.Format("MstDataCacheTable: {0} {1}", cache.Key, cache.Value.Data.Cast<object>().Count()));
            }

            return Array.Empty<T>();
        }

        public void Dispose()
        {
            if (_isDisposed)
            {
                return;
            }

            _isDisposed = true;

            _mstDataCacheTable.Clear();

            ApplicationLog.Log(nameof(MstDataLocalJsonDataStore), "Dispose");
        }

        byte[] Decrypt(byte[] data, IMasterDataDecryptor decryptor = null)
        {
            if (decryptor == null)
            {
                return data;
            }

            var decryptedData = decryptor.Decrypt(data);
            return decryptedData;
        }

        IEnumerable<MstCacheData> GenerateCacheTable(MasterType masterType, Language language, byte[] data)
        {
            var jsonString = Encoding.UTF8.GetString(data);

            // NOTE: 日付データは全てUTCとして取り扱うため明示的に設定する
            //       明示的に指定されていない場合DateTimeのKindはLocalとなる
            var jsonSerializerSettings = new JsonSerializerSettings
            {
                DateTimeZoneHandling = DateTimeZoneHandling.Utc,
            };
            jsonSerializerSettings.Converters.Add(new JsonLanguageEnumConverter());

            // NOTE: マスターデータの集合体をデシリアライズする
            IMstDataToCacheDataConverter converter;
            switch (masterType)
            {
                case MasterType.Mst:
                    var mstData = JsonConvert.DeserializeObject<MstData>(jsonString, jsonSerializerSettings);
                    converter = new MstDataToCacheDataConverter(mstData);
                    break;
                case MasterType.Opr:
                    var oprData = JsonConvert.DeserializeObject<OprData>(jsonString, jsonSerializerSettings);
                    converter = new OprDataToCacheDataConverter(oprData);
                    break;
                case MasterType.MstI18n:
                    var mstI18nData = JsonConvert.DeserializeObject<MstI18nData>(jsonString, jsonSerializerSettings);
                    converter = new MstI18nDataToCacheDataConverter(mstI18nData);
                    break;
                case MasterType.OprI18n:
                    var oprI18nData = JsonConvert.DeserializeObject<OprI18nData>(jsonString, jsonSerializerSettings);
                    converter = new OprI18nDataToCacheDataConverter(oprI18nData);
                    break;
                default:
                    throw new InvalidOperationException("masterType is invalid.");
            }

            ApplicationLog.Log(
                nameof(MstDataLocalJsonDataStore),
                ZString.Format("GenerateCacheTable: {0} {1}", masterType, language));

            return converter.Convert(language);
        }

        void AddCacheTable(IEnumerable<MstCacheData> mstLocalCacheData)
        {
            foreach (var data in mstLocalCacheData)
            {
                AddCacheTable(data);
            }
        }

        void AddCacheTable(MstCacheData mstCacheData)
        {
            if (mstCacheData.Data == null)
            {
                throw new ArgumentNullException(ZString.Format("data is null. type: {0}", mstCacheData.Type));
            }

            // NOTE: キャッシュとして情報を格納する
            _mstDataCacheTable.Add(mstCacheData.Type, mstCacheData);

            ApplicationLog.Log(
                nameof(MstDataLocalJsonDataStore),
                ZString.Format("AddCacheTable: {0} {1}", mstCacheData.Type, mstCacheData.Data.Cast<object>().Count()));
        }

        void RemoveCacheTable(MasterType masterType)
        {
            var keys =
                _mstDataCacheTable.Values
                    .Where(value => value.MasterType == masterType)
                    .Select(value => value.Type)
                    .ToList();

            ApplicationLog.Log(
                nameof(MstDataLocalJsonDataStore),
                ZString.Format("RemoveCacheTable: {0} {1}", masterType, keys.Count));

            foreach (var key in keys)
            {
                ApplicationLog.Log(nameof(MstDataLocalJsonDataStore), ZString.Format("RemoveCacheTable: {0}", key));
                _mstDataCacheTable.Remove(key);
            }
        }
    }
}
