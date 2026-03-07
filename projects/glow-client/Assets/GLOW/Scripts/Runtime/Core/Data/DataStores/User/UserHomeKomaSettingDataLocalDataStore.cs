using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data.User;
using GLOW.Core.Data.Modules.Serializer;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.Home.Domain.ValueObjects;
using Newtonsoft.Json;
using Runtime.PlayerPrefs;
using UnityEngine;

namespace GLOW.Core.Data.DataStores
{
    public class UserHomeKomaSettingDataLocalDataStore : IUserHomeKomaSettingDataLocalDataStore
    {
        // HomeMainView
        static string KeySetCurrentMstHomeKomaPatternId => "GLOW/SetCurrentMstHomeKomaPatternId";
        // KomaSettingView
        const string PrefsKey = "GLOW.Data.DataStores.UserHomeKomaSettingDataLocalDataStore.KEY";
        const string VersionKey = "GLOW.Data.DataStores.UserHomeKomaSettingDataLocalDataStoreVersion.KEY";
        const int CurrentMigrateVersion = 1;//要素が増えたときはこのversionを更新する

        JsonSerializerSettings _serializerSettings = new JsonSerializerSettings
        {
            NullValueHandling = NullValueHandling.Include,
            DefaultValueHandling = DefaultValueHandling.Include,
            Converters = new List<JsonConverter>
            {
                new ObscuredStringJsonConverter(),
                new ObscuredIntJsonConverter()
            }
        };

        IReadOnlyList<UserHomeKomaSettingData> _cachedUserHomeKomaSettingDataList;
        IReadOnlyList<UserHomeKomaSettingData> CachedUserHomeKomaSettingDataList => _cachedUserHomeKomaSettingDataList;
        MasterDataId _currentMstHomeKomaPatternId;

        bool IUserHomeKomaSettingDataLocalDataStore.IsLoaded => CachedUserHomeKomaSettingDataList != null;
        void IUserHomeKomaSettingDataLocalDataStore.Load()
        {
            Load();
        }

        void Load()
        {
            (var dataList, var savedVersion) = Create();
            _cachedUserHomeKomaSettingDataList = dataList;

            // マイグレーションが発生した場合はversionを更新して保存する
            if (savedVersion < CurrentMigrateVersion)
            {
                // 新規追加した要素をデフォルト値としてユーザーデータ更新
                var updatedJson = JsonConvert.SerializeObject(dataList, _serializerSettings);
                EncryptionPlayerPrefs.SetString(PrefsKey, updatedJson);
                // バージョン管理周りの更新
                EncryptionPlayerPrefs.SetInt(VersionKey, CurrentMigrateVersion);
                // 保存実行
                EncryptionPlayerPrefs.Save();
            }
        }

        void IUserHomeKomaSettingDataLocalDataStore.Save(UserHomeKomaSettingData userHomeKomaSettingData)
        {
            // 更新後のリストを作成
            var updatedDataList = CreateUpdatedDataList(userHomeKomaSettingData);

            // ユーザーデータ更新
            var updatedJson = JsonConvert.SerializeObject(updatedDataList, _serializerSettings);
            EncryptionPlayerPrefs.SetString(PrefsKey, updatedJson);

            // バージョン管理周りの更新
            EncryptionPlayerPrefs.SetInt(VersionKey, CurrentMigrateVersion);

            // 保存実行
            EncryptionPlayerPrefs.Save();

            _cachedUserHomeKomaSettingDataList = updatedDataList;
        }

        IReadOnlyList<UserHomeKomaSettingData> CreateUpdatedDataList(UserHomeKomaSettingData userHomeKomaSettingData)
        {
            // 既存のリストから該当パターンを更新または追加
            var list = CachedUserHomeKomaSettingDataList?.ToList() ?? new List<UserHomeKomaSettingData>();

            // 置き換えまたは追加
            var result = list.ReplaceOrAdd(
                d => d.MstHomeKomaPatternId == userHomeKomaSettingData.MstHomeKomaPatternId,
                userHomeKomaSettingData);
            return result;
        }

        void IUserHomeKomaSettingDataLocalDataStore.Delete()
        {
            EncryptionPlayerPrefs.DeleteKey(PrefsKey);
            EncryptionPlayerPrefs.Save();
        }

        IReadOnlyList<UserHomeKomaSettingData> IUserHomeKomaSettingDataLocalDataStore.GetAll()
        {
            if (CachedUserHomeKomaSettingDataList == null)
            {
                Load();
            }
            return CachedUserHomeKomaSettingDataList;
        }

        #region HomeMainView
        MasterDataId IUserHomeKomaSettingDataLocalDataStore.CurrentMstHomeKomaPatternId => GetCurrentMstHomeKomaPatternId();
        MasterDataId GetCurrentMstHomeKomaPatternId()
        {
            var value = EncryptionPlayerPrefs.GetString(KeySetCurrentMstHomeKomaPatternId, "");
            return string.IsNullOrEmpty(value) ? MasterDataId.Empty : new MasterDataId(value);
        }

        void IUserHomeKomaSettingDataLocalDataStore.SetCurrentMstHomeKomaPatternId(MasterDataId mstHomeKomaPatternId)
        {
            EncryptionPlayerPrefs.SetString(KeySetCurrentMstHomeKomaPatternId, mstHomeKomaPatternId.Value);
            EncryptionPlayerPrefs.Save();
        }

        IReadOnlyDictionary<HomeMainKomaUnitAssetSetPlaceIndex, MasterDataId>
            IUserHomeKomaSettingDataLocalDataStore.GetCurrentHomeKomaMstUnitIds()
        {
            if (CachedUserHomeKomaSettingDataList == null)
            {
                Load();
            }
            var currentMstHomeKomaPatternId = GetCurrentMstHomeKomaPatternId();


            var targetData = CachedUserHomeKomaSettingDataList
                .FirstOrDefault(d =>d.MstHomeKomaPatternId == currentMstHomeKomaPatternId
                    ,null);

            if (targetData == null)
            {
                return new Dictionary<HomeMainKomaUnitAssetSetPlaceIndex, MasterDataId>();
            }

            return targetData.UserHomeKomaUnitSettingDatas
                .ToDictionary(
                    d => new HomeMainKomaUnitAssetSetPlaceIndex(d.PlaceIndex.Value),
                    d =>
                    {
                        var str = d.MstUnitId?.Value;
                        return string.IsNullOrEmpty(str) ? MasterDataId.Empty : d.MstUnitId;
                    });
        }
        #endregion

        (IReadOnlyList<UserHomeKomaSettingData> dataList, int savedVersion) Create()
        {
            // データが存在しない場合は新規作成
            if (!EncryptionPlayerPrefs.HasKey(PrefsKey))
            {
                return (new List<UserHomeKomaSettingData>(), 0);
            }

            var savedVersion = EncryptionPlayerPrefs.GetInt(VersionKey, 0);
            var jsonString = EncryptionPlayerPrefs.GetString(PrefsKey);

            var dataList = JsonConvert.DeserializeObject<List<UserHomeKomaSettingData>>(jsonString, _serializerSettings)
                           ?? new List<UserHomeKomaSettingData>();

            // バージョンごとにマイグレーション処理記述例
            // if (savedVersion < 2)
            // {
            //     // v1 -> v2 のマイグレーション
            //     // 例: 新しいプロパティにデフォルト値を設定
            //     dataList = dataList.Select(data => new UserHomeKomaSettingData(
            //         data.MstHomeKomaPatternId,
            //         data.UserHomeKomaUnitSettingDatas,
            //         newProperty: デフォルト値 // 新しい要素
            //     )).ToList();
            // }
            return (dataList, savedVersion);
        }
    }
}
